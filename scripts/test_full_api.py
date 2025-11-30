#!/usr/bin/env python3
"""
CityResQ360 - Full API Test from Postman Collection
Auto-parse Postman collection and test all endpoints
"""

import json
import requests
from pathlib import Path
from datetime import datetime
from typing import Dict, List, Tuple
from dataclasses import dataclass
from enum import Enum


class TestStatus(Enum):
    SUCCESS = "OK"
    FAILED = "FAIL"
    ERROR = "ERR"
    SKIPPED = "SKIP"


@dataclass
class APITestResult:
    folder: str
    endpoint: str
    method: str
    status: TestStatus
    status_code: int = None
    response_time: float = None
    error_message: str = None


class PostmanCollectionTester:
    def __init__(self, collection_path: str, base_url: str, image_path: str = None):
        self.collection_path = collection_path
        self.base_url = base_url
        self.image_path = image_path
        self.auth_token = None
        self.test_results: List[APITestResult] = []
        self.session = requests.Session()
        
        # Skip these endpoints
        self.skip_endpoints = [
            'Register',
            'Login',  # Already logged in once
            'Forgot Password',
            'Reset Password',
            'Logout',  # Don't logout during testing
            'Refresh Token',  # Token is fresh
        ]
        
    def load_collection(self) -> Dict:
        """Load Postman collection JSON"""
        with open(self.collection_path, 'r', encoding='utf-8') as f:
            return json.load(f)
    
    def login(self, email: str, password: str) -> bool:
        """Login and get auth token"""
        print(f"\n{'='*80}")
        print("AUTHENTICATING...")
        print(f"{'='*80}\n")
        
        url = f"{self.base_url}/api/v1/auth/login"
        payload = {
            "email": email,
            "mat_khau": password,
            "remember": True
        }
        
        try:
            response = self.session.post(url, json=payload, timeout=10)
            print(f"URL: {url}")
            print(f"Email: {email}")
            print(f"Status: {response.status_code}")
            
            if response.status_code == 200:
                data = response.json()
                if data.get('data', {}).get('token'):
                    self.auth_token = data['data']['token']
                    print(f"✅ Login successful!")
                    print(f"Token: {self.auth_token[:30]}...")
                    self.session.headers.update({
                        'Authorization': f'Bearer {self.auth_token}',
                        'Accept': 'application/json'
                    })
                    return True
            print(f"❌ Login failed: {response.status_code}")
            return False
        except Exception as e:
            print(f"❌ Login error: {str(e)}")
            return False
    
    def extract_requests(self, items: List, folder_name: str = "") -> List[Tuple]:
        """Recursively extract all requests from collection"""
        requests_list = []
        
        for item in items:
            if 'item' in item:
                # This is a folder
                sub_folder = f"{folder_name}/{item['name']}" if folder_name else item['name']
                requests_list.extend(self.extract_requests(item['item'], sub_folder))
            elif 'request' in item:
                # This is a request
                requests_list.append((folder_name, item))
        
        return requests_list
    
    def build_url(self, url_obj: Dict) -> str:
        """Build full URL from Postman URL object"""
        if isinstance(url_obj, str):
            return url_obj.replace("{{base_url}}", self.base_url)
        
        # Build from components
        path = '/'.join(url_obj.get('path', []))
        url = f"{self.base_url}/{path}"
        
        # Add query parameters
        query_params = url_obj.get('query', [])
        if query_params:
            params = []
            for param in query_params:
                if param.get('disabled') != True:
                    key = param.get('key', '')
                    value = param.get('value', '')
                    if key:
                        params.append(f"{key}={value}")
            if params:
                url += '?' + '&'.join(params)
        
        return url
    
    def test_endpoint(self, folder: str, request_item: Dict) -> APITestResult:
        """Test a single endpoint"""
        request_obj = request_item['request']
        name = request_item.get('name', 'Unnamed')
        method = request_obj.get('method', 'GET')
        
        # Skip if in skip list
        if name in self.skip_endpoints:
            return APITestResult(
                folder=folder,
                endpoint=name,
                method=method,
                status=TestStatus.SKIPPED,
                error_message="Skipped by configuration"
            )
        
        # Build URL
        url = self.build_url(request_obj.get('url', {}))
        
        # Skip if destructive without confirmation
        if method in ['DELETE'] and 'delete' in name.lower():
            return APITestResult(
                folder=folder,
                endpoint=name,
                method=method,
                status=TestStatus.SKIPPED,
                error_message="Destructive operation skipped"
            )
        
        # Prepare headers - use session headers which already have auth token
        # Don't parse headers from collection to avoid overriding our token
        additional_headers = {}
        for header in request_obj.get('header', []):
            if header.get('disabled') != True:
                key = header.get('key', '')
                value = header.get('value', '')
                # Skip Authorization header - use session's token instead
                if key and value and key.lower() != 'authorization':
                    additional_headers[key] = value
        
        # Special handling for Upload Media
        if 'Upload Media' in name and self.image_path and Path(self.image_path).exists():
            try:
                start_time = datetime.now()
                
                with open(self.image_path, 'rb') as f:
                    files = {'file': (Path(self.image_path).name, f, 'image/png')}
                    data = {
                        'type': 'image',
                        'lien_ket_den': 'phan_anh',
                        'mo_ta': 'Test upload from automation'
                    }
                    
                    # For file upload, only use Authorization header
                    upload_headers = {'Authorization': f'Bearer {self.auth_token}'}
                    
                    response = self.session.post(
                        url,
                        data=data,
                        files=files,
                        headers=upload_headers,
                        timeout=30
                    )
                
                end_time = datetime.now()
                response_time = (end_time - start_time).total_seconds()
                
                if response.status_code < 400:
                    status = TestStatus.SUCCESS
                else:
                    status = TestStatus.FAILED
                
                return APITestResult(
                    folder=folder,
                    endpoint=name,
                    method=method,
                    status=status,
                    status_code=response.status_code,
                    response_time=response_time
                )
            except Exception as e:
                return APITestResult(
                    folder=folder,
                    endpoint=name,
                    method=method,
                    status=TestStatus.ERROR,
                    error_message=str(e)
                )
        
        # Prepare body
        body = None
        body_obj = request_obj.get('body', {})
        if body_obj.get('mode') == 'raw':
            raw_body = body_obj.get('raw', '{}')
            try:
                body = json.loads(raw_body)
            except:
                body = None
        
        # Execute request
        try:
            start_time = datetime.now()
            
            # Merge additional headers with session headers
            request_headers = {**self.session.headers, **additional_headers}
            
            response = self.session.request(
                method=method,
                url=url,
                json=body if body else None,
                headers=request_headers,
                timeout=10
            )
            
            end_time = datetime.now()
            response_time = (end_time - start_time).total_seconds()
            
            # Determine status
            if response.status_code < 400:
                status = TestStatus.SUCCESS
            else:
                status = TestStatus.FAILED
            
            return APITestResult(
                folder=folder,
                endpoint=name,
                method=method,
                status=status,
                status_code=response.status_code,
                response_time=response_time
            )
            
        except Exception as e:
            return APITestResult(
                folder=folder,
                endpoint=name,
                method=method,
                status=TestStatus.ERROR,
                error_message=str(e)
            )
    
    def test_all_endpoints(self):
        """Test all endpoints from collection"""
        print(f"\n{'='*80}")
        print("LOADING POSTMAN COLLECTION...")
        print(f"{'='*80}\n")
        
        collection = self.load_collection()
        collection_name = collection.get('info', {}).get('name', 'Unknown')
        print(f"Collection: {collection_name}")
        
        # Extract all requests
        requests_list = self.extract_requests(collection.get('item', []))
        total_requests = len(requests_list)
        print(f"Total endpoints found: {total_requests}")
        
        print(f"\n{'='*80}")
        print("TESTING ENDPOINTS...")
        print(f"{'='*80}\n")
        
        current_folder = ""
        for folder, request_item in requests_list:
            # Print folder header
            if folder != current_folder:
                current_folder = folder
                print(f"\n[{folder}]")
                print("-" * 80)
            
            # Test endpoint
            result = self.test_endpoint(folder, request_item)
            self.test_results.append(result)
            self._print_result(result)
    
    def _print_result(self, result: APITestResult):
        """Print test result"""
        if result.status == TestStatus.SUCCESS:
            print(f"  ✅ [{result.method:6}] {result.endpoint:45} {result.status_code} ({result.response_time:.2f}s)")
        elif result.status == TestStatus.FAILED:
            print(f"  ❌ [{result.method:6}] {result.endpoint:45} {result.status_code}")
        elif result.status == TestStatus.SKIPPED:
            print(f"  ⏭️  [{result.method:6}] {result.endpoint:45} SKIPPED")
        else:
            print(f"  💥 [{result.method:6}] {result.endpoint:45} ERROR")
    
    def generate_report(self, output_file: str = None):
        """Generate test report"""
        total = len(self.test_results)
        success = sum(1 for r in self.test_results if r.status == TestStatus.SUCCESS)
        failed = sum(1 for r in self.test_results if r.status == TestStatus.FAILED)
        error = sum(1 for r in self.test_results if r.status == TestStatus.ERROR)
        skipped = sum(1 for r in self.test_results if r.status == TestStatus.SKIPPED)
        
        success_rate = (success / (total - skipped) * 100) if (total - skipped) > 0 else 0
        
        report = f"""
{'='*80}
FULL API TEST REPORT
{'='*80}

Overall Statistics:
   Total Endpoints: {total}
   Tested: {total - skipped}
   Skipped: {skipped}
   ✅ Success: {success} ({success_rate:.1f}%)
   ❌ Failed: {failed}
   💥 Errors: {error}

{'='*80}
DETAILED RESULTS BY FOLDER
{'='*80}

"""
        
        # Group by folder
        folders = {}
        for result in self.test_results:
            if result.folder not in folders:
                folders[result.folder] = []
            folders[result.folder].append(result)
        
        # Print by folder
        for folder, results in folders.items():
            report += f"\n[{folder}]\n"
            report += "-" * 80 + "\n"
            
            for result in results:
                status_icon = {
                    TestStatus.SUCCESS: "✅",
                    TestStatus.FAILED: "❌",
                    TestStatus.SKIPPED: "⏭️",
                    TestStatus.ERROR: "💥"
                }[result.status]
                
                if result.status == TestStatus.SUCCESS:
                    report += f"{status_icon} [{result.method:6}] {result.endpoint:45} {result.status_code} ({result.response_time:.2f}s)\n"
                elif result.status == TestStatus.FAILED:
                    report += f"{status_icon} [{result.method:6}] {result.endpoint:45} {result.status_code}\n"
                elif result.status == TestStatus.SKIPPED:
                    report += f"{status_icon} [{result.method:6}] {result.endpoint:45} SKIPPED\n"
                else:
                    report += f"{status_icon} [{result.method:6}] {result.endpoint:45} ERROR: {result.error_message}\n"
        
        report += f"\n{'='*80}\n"
        report += f"Test completed at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n"
        report += f"{'='*80}\n"
        
        print(report)
        
        if output_file:
            with open(output_file, 'w', encoding='utf-8') as f:
                f.write(report)
            print(f"\n📄 Report saved to: {output_file}")


def main():
    # Configuration
    COLLECTION_PATH = "/Volumes/MyVolume/Laravel/CityResQ360-DTUDZ/collections/postman/API_MNM_2025_1.postman_collection.json"
    BASE_URL = "http://localhost:8000"
    EMAIL = "nguyenvanan@gmail.com"
    PASSWORD = "password123"
    IMAGE_PATH = "/Volumes/MyVolume/Laravel/CityResQ360-DTUDZ/static/logo.png"
    OUTPUT_FILE = "full_api_test_report.txt"
    
    # Create tester
    tester = PostmanCollectionTester(COLLECTION_PATH, BASE_URL, IMAGE_PATH)
    
    # Login
    if not tester.login(EMAIL, PASSWORD):
        print("\n❌ Login failed. Cannot proceed.")
        return 1
    
    # Test all endpoints
    tester.test_all_endpoints()
    
    # Generate report
    tester.generate_report(OUTPUT_FILE)
    
    # Return exit code
    failed_count = sum(1 for r in tester.test_results if r.status == TestStatus.FAILED)
    error_count = sum(1 for r in tester.test_results if r.status == TestStatus.ERROR)
    
    return 1 if (failed_count > 0 or error_count > 0) else 0


if __name__ == '__main__':
    exit(main())
