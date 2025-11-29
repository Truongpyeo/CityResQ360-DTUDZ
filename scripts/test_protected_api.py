#!/usr/bin/env python3
"""
CityResQ360 - Test Protected APIs
Test all APIs that require authentication token
"""

import json
import requests
from pathlib import Path
from datetime import datetime
from typing import Dict, List
from dataclasses import dataclass
from enum import Enum


class TestStatus(Enum):
    SUCCESS = "OK"
    FAILED = "FAIL"
    ERROR = "ERR"


@dataclass
class APITestResult:
    endpoint: str
    method: str
    status: TestStatus
    status_code: int = None
    response_time: float = None
    error_message: str = None
    response_data: Dict = None


class ProtectedAPITester:
    def __init__(self, base_url: str):
        self.base_url = base_url
        self.auth_token = None
        self.test_results: List[APITestResult] = []
        self.session = requests.Session()
        self.user_id = None
        
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
            response = self.session.post(
                url,
                json=payload,
                headers={'Accept': 'application/json', 'Content-Type': 'application/json'},
                timeout=10
            )
            
            print(f"URL: {url}")
            print(f"Email: {email}")
            print(f"Password: {'*' * len(password)}")
            print(f"Status: {response.status_code}")
            
            if response.status_code == 200:
                data = response.json()
                if data.get('data', {}).get('token'):
                    self.auth_token = data['data']['token']
                    self.user_id = data['data'].get('user', {}).get('id')
                    print(f"Login successful!")
                    print(f"Token: {self.auth_token[:30]}...")
                    print(f"User ID: {self.user_id}")
                    
                    # Update session headers
                    self.session.headers.update({
                        'Authorization': f'Bearer {self.auth_token}',
                        'Accept': 'application/json'
                    })
                    return True
                else:
                    print(f"No token in response")
                    print(f"Response: {data}")
                    return False
            else:
                print(f"Login failed: {response.status_code}")
                try:
                    print(f"Response: {response.json()}")
                except:
                    print(f"Response: {response.text}")
                return False
                
        except Exception as e:
            print(f"Login error: {str(e)}")
            return False
    
    def test_endpoint(self, method: str, endpoint: str, name: str, 
                     payload: Dict = None, files: Dict = None) -> APITestResult:
        """Test a single endpoint"""
        url = f"{self.base_url}{endpoint}"
        
        try:
            start_time = datetime.now()
            
            if files:
                # For file upload
                headers = {'Authorization': f'Bearer {self.auth_token}'}
                response = self.session.request(
                    method=method,
                    url=url,
                    data=payload,
                    files=files,
                    headers=headers,
                    timeout=30
                )
            else:
                response = self.session.request(
                    method=method,
                    url=url,
                    json=payload,
                    timeout=10
                )
            
            end_time = datetime.now()
            response_time = (end_time - start_time).total_seconds()
            
            # Parse response
            try:
                response_data = response.json()
            except:
                response_data = {"raw": response.text}
            
            # Determine status
            if response.status_code < 400:
                status = TestStatus.SUCCESS
            else:
                status = TestStatus.FAILED
            
            return APITestResult(
                endpoint=name,
                method=method,
                status=status,
                status_code=response.status_code,
                response_time=response_time,
                response_data=response_data
            )
            
        except Exception as e:
            return APITestResult(
                endpoint=name,
                method=method,
                status=TestStatus.ERROR,
                error_message=str(e)
            )
    
    def test_protected_apis(self, image_path: str = None):
        """Test all protected APIs"""
        print(f"\n{'='*80}")
        print("TESTING PROTECTED APIs")
        print(f"{'='*80}\n")
        
        # 1. Authentication
        print("\n[1] Authentication")
        print("-" * 80)
        result = self.test_endpoint("GET", "/api/v1/auth/me", "Get Current User")
        self.test_results.append(result)
        self._print_result(result)
        
        # 2. Reports
        print("\n[2] Reports")
        print("-" * 80)
        
        tests = [
            ("GET", "/api/v1/reports", "List All Reports"),
            ("GET", "/api/v1/reports/my", "Get My Reports"),
            ("GET", "/api/v1/reports/trending?limit=10", "Get Trending Reports"),
            ("GET", "/api/v1/reports/nearby?vi_do=16.0544&kinh_do=108.2022&radius=5000", "Get Nearby Reports"),
        ]
        
        for method, endpoint, name in tests:
            result = self.test_endpoint(method, endpoint, name)
            self.test_results.append(result)
            self._print_result(result)
        
        # 3. Comments
        print("\n[3] Comments")
        print("-" * 80)
        result = self.test_endpoint("GET", "/api/v1/reports/1/comments", "Get Comments")
        self.test_results.append(result)
        self._print_result(result)
        
        # 4. Media - PRIORITY
        print("\n[4] Media - PRIORITY")
        print("-" * 80)
        
        if image_path and Path(image_path).exists():
            print(f"Testing Image Upload: {Path(image_path).name}")
            result = self._test_upload_media(image_path)
            self.test_results.append(result)
            self._print_result(result)
        else:
            print(f"Warning: Image not found: {image_path}")
        
        result = self.test_endpoint("GET", "/api/v1/media/my?page=1", "Get My Media")
        self.test_results.append(result)
        self._print_result(result)
        
        # 5. Map & Location
        print("\n[5] Map & Location")
        print("-" * 80)
        
        tests = [
            ("GET", "/api/v1/map/reports?bounds=10.7,106.6,10.8,106.8", "Get Map Reports"),
            ("GET", "/api/v1/map/heatmap?days=7", "Get Heatmap"),
            ("GET", "/api/v1/map/clusters?zoom=12", "Get Clusters"),
        ]
        
        for method, endpoint, name in tests:
            result = self.test_endpoint(method, endpoint, name)
            self.test_results.append(result)
            self._print_result(result)
        
        # 6. Wallet
        print("\n[6] Wallet & CityPoints")
        print("-" * 80)
        
        tests = [
            ("GET", "/api/v1/wallet", "Get Wallet"),
            ("GET", "/api/v1/wallet/transactions", "Get Transactions"),
            ("GET", "/api/v1/wallet/rewards", "Get Rewards"),
        ]
        
        for method, endpoint, name in tests:
            result = self.test_endpoint(method, endpoint, name)
            self.test_results.append(result)
            self._print_result(result)
        
        # 7. Notifications
        print("\n[7] Notifications")
        print("-" * 80)
        
        tests = [
            ("GET", "/api/v1/notifications", "Get Notifications"),
            ("GET", "/api/v1/notifications/unread", "Get Unread"),
        ]
        
        for method, endpoint, name in tests:
            result = self.test_endpoint(method, endpoint, name)
            self.test_results.append(result)
            self._print_result(result)
        
        # 8. Stats
        print("\n[8] User Statistics")
        print("-" * 80)
        
        tests = [
            ("GET", "/api/v1/stats/overview", "Get Overview"),
            ("GET", "/api/v1/stats/categories", "Get Categories Stats"),
            ("GET", "/api/v1/stats/timeline?period=month", "Get Timeline"),
        ]
        
        for method, endpoint, name in tests:
            result = self.test_endpoint(method, endpoint, name)
            self.test_results.append(result)
            self._print_result(result)
    
    def _test_upload_media(self, image_path: str) -> APITestResult:
        """Test media upload"""
        try:
            url = f"{self.base_url}/api/v1/media/upload"
            
            with open(image_path, 'rb') as f:
                files = {'file': ('test.png', f, 'image/png')}
                data = {
                    'type': 'image',
                    'lien_ket_den': 'phan_anh',
                    'mo_ta': 'Test from API script'
                }
                
                start_time = datetime.now()
                response = self.session.post(
                    url,
                    data=data,
                    files=files,
                    headers={'Authorization': f'Bearer {self.auth_token}'},
                    timeout=30
                )
                end_time = datetime.now()
                response_time = (end_time - start_time).total_seconds()
            
            try:
                response_data = response.json()
            except:
                response_data = {"raw": response.text}
            
            if response.status_code < 400:
                status = TestStatus.SUCCESS
            else:
                status = TestStatus.FAILED
            
            return APITestResult(
                endpoint="Upload Media",
                method="POST",
                status=status,
                status_code=response.status_code,
                response_time=response_time,
                response_data=response_data
            )
            
        except Exception as e:
            return APITestResult(
                endpoint="Upload Media",
                method="POST",
                status=TestStatus.ERROR,
                error_message=str(e)
            )
    
    def _print_result(self, result: APITestResult):
        """Print result"""
        if result.status == TestStatus.SUCCESS:
            print(f"  OK   [{result.method:6}] {result.endpoint:40} {result.status_code} ({result.response_time:.2f}s)")
            if 'upload' in result.endpoint.lower() and result.response_data:
                data = result.response_data.get('data', {})
                if data.get('id'):
                    print(f"       Media ID: {data.get('id')}")
                    print(f"       URL: {data.get('url', 'N/A')[:60]}")
        elif result.status == TestStatus.FAILED:
            print(f"  FAIL [{result.method:6}] {result.endpoint:40} {result.status_code}")
            if result.response_data and isinstance(result.response_data, dict):
                error = result.response_data.get('message', result.response_data.get('error', ''))
                if error:
                    print(f"       Error: {error}")
        else:
            print(f"  ERR  [{result.method:6}] {result.endpoint:40} ({result.error_message})")
    
    def generate_report(self, output_file: str = None):
        """Generate report"""
        total = len(self.test_results)
        success = sum(1 for r in self.test_results if r.status == TestStatus.SUCCESS)
        failed = sum(1 for r in self.test_results if r.status == TestStatus.FAILED)
        error = sum(1 for r in self.test_results if r.status == TestStatus.ERROR)
        
        success_rate = (success / total * 100) if total > 0 else 0
        
        report = f"""
{'='*80}
PROTECTED APIs TEST REPORT
{'='*80}

Overall Statistics:
   Total Endpoints Tested: {total}
   Successful: {success} ({success_rate:.1f}%)
   Failed: {failed}
   Errors: {error}

{'='*80}
DETAILED RESULTS
{'='*80}

"""
        
        for result in self.test_results:
            if result.status == TestStatus.SUCCESS:
                report += f"OK   [{result.method:6}] {result.endpoint:45} {result.status_code} ({result.response_time:.2f}s)\n"
                if 'upload' in result.endpoint.lower() and result.response_data:
                    data = result.response_data.get('data', {})
                    if data.get('id'):
                        report += f"     Media ID: {data.get('id')}\n"
                        report += f"     URL: {data.get('url', 'N/A')}\n"
            elif result.status == TestStatus.FAILED:
                report += f"FAIL [{result.method:6}] {result.endpoint:45} {result.status_code}\n"
                if result.response_data and isinstance(result.response_data, dict):
                    error = result.response_data.get('message', result.response_data.get('error', ''))
                    if error:
                        report += f"     Error: {error}\n"
            else:
                report += f"ERR  [{result.method:6}] {result.endpoint:45} Error: {result.error_message}\n"
        
        report += f"\n{'='*80}\n"
        report += f"Test completed at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n"
        report += f"{'='*80}\n"
        
        print(report)
        
        if output_file:
            with open(output_file, 'w', encoding='utf-8') as f:
                f.write(report)
            print(f"\nReport saved to: {output_file}")


def main():
    BASE_URL = "https://api.cityresq360.io.vn"
    EMAIL = "nguyenvanan@gmail.com"
    PASSWORD = "password123"
    IMAGE_PATH = r"C:\Users\Admin\Downloads\1.png"
    OUTPUT_FILE = "protected_api_test_report.txt"
    
    tester = ProtectedAPITester(BASE_URL)
    
    if not tester.login(EMAIL, PASSWORD):
        print("\nLogin failed. Cannot proceed.")
        return 1
    
    tester.test_protected_apis(IMAGE_PATH)
    tester.generate_report(OUTPUT_FILE)
    
    failed_count = sum(1 for r in tester.test_results if r.status == TestStatus.FAILED)
    error_count = sum(1 for r in tester.test_results if r.status == TestStatus.ERROR)
    
    return 1 if (failed_count > 0 or error_count > 0) else 0


if __name__ == '__main__':
    exit(main())
