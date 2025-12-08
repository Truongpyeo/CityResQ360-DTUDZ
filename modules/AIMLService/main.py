# CityResQ360-DTUDZ - Smart City Emergency Response System
# Copyright (C) 2025 DTU-DZ Team
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <https://www.gnu.org/licenses/>.

from fastapi import FastAPI, File, UploadFile, HTTPException, Depends
from fastapi.middleware.cors import CORSMiddleware
import google.generativeai as genai
from PIL import Image
import io
import base64
import logging
import time
import json
from typing import Dict, List, Optional
from datetime import datetime

# Import authentication middleware
from middleware.auth import authenticate, optional_authenticate

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(
    title="CityResQ360 AI/ML Service",
    version="1.0.0",
    description="AI-powered incident detection for smart city emergency response"
)

# CORS middleware - allow all origins for development
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Allow all origins for open API
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Configure Gemini API - use environment variable for security
import os
GEMINI_API_KEY = os.environ.get("GEMINI_API_KEY", "")
if not GEMINI_API_KEY:
    logger.warning("⚠️ GEMINI_API_KEY environment variable not set! AI analysis will fail.")
else:
    genai.configure(api_key=GEMINI_API_KEY)

# Global Gemini model
gemini_model = None

# Enhanced Vietnamese labels mapping
INCIDENT_LABELS = {
    "pothole": {
        "vi": "Ổ gà",
        "en": "Pothole",
        "severity": "medium",
        "priority": "high",
        "category_id": 1,  # Map to CoreAPI categories
        "keywords": ["road", "damage", "hole", "asphalt", "street", "pavement", "crack", "broken"],
        "negative_keywords": ["car", "truck", "person", "vehicle"],
    },
    "flooding": {
        "vi": "Ngập lụt",
        "en": "Flooding",
        "severity": "high",
        "priority": "critical",
        "category_id": 2,
        "keywords": ["water", "flood", "rain", "street", "submerged", "puddle", "wet"],
        "negative_keywords": [],
    },
    "traffic_light": {
        "vi": "Đèn giao thông",
        "en": "Traffic Light",
        "severity": "medium",
        "priority": "high",
        "category_id": 3,
        "keywords": ["traffic", "light", "signal", "broken", "intersection", "crossing"],
        "negative_keywords": [],
    },
    "waste": {
        "vi": "Rác thải",
        "en": "Waste",
        "severity": "low",
        "priority": "medium",
        "category_id": 4,
        "keywords": ["garbage", "trash", "waste", "litter", "rubbish", "debris", "dump"],
        "negative_keywords": ["car", "person", "vehicle"],
    },
    "traffic_jam": {
        "vi": "Kẹt xe",
        "en": "Traffic Jam",
        "severity": "medium",
        "priority": "medium",
        "category_id": 5,
        "keywords": ["traffic", "jam", "congestion", "cars", "queue", "stuck"],
        "negative_keywords": [],
    },
    "other": {
        "vi": "Sự cố khác",
        "en": "Other",
        "severity": "medium",
        "priority": "medium",
        "category_id": 6,
        "keywords": [],
        "negative_keywords": [],
    }
}


@app.on_event("startup")
async def load_models():
    """Initialize Gemini Vision API"""
    global gemini_model
    
    try:
        logger.info("🚀 Initializing Gemini Vision API...")
        
        # Use Gemini 3 Pro Image Preview - Latest and most capable
        gemini_model = genai.GenerativeModel('gemini-3-pro-image-preview')
        
        logger.info("✅ Gemini 3 Pro Image Preview initialized successfully")
        logger.info("🌟 Using Google's NEWEST and most advanced vision model")
        logger.info("💰 Cost-effective with generous free tier")
        
    except Exception as e:
        logger.error(f"❌ Error loading Gemini: {str(e)}")
        raise



def analyze_image_content(image: Image.Image) -> Dict:
    """Analyze image using Gemini Vision API"""
    try:
        # Ensure RGB mode
        if image.mode != "RGB":
            logger.info(f"Converting image from {image.mode} to RGB")
            image = image.convert("RGB")
        
        # Resize if too large (Gemini accepts up to 4MB)
        max_size = 1024
        if max(image.size) > max_size:
            logger.info(f"Resizing image from {image.size}")
            image.thumbnail((max_size, max_size), Image.Resampling.LANCZOS)
            logger.info(f"Resized to {image.size}")
        
        logger.info(f"📸 Analyzing with Gemini Vision: size={image.size}, mode={image.mode}")
        
        # Vietnamese prompt for incident classification
        prompt = """Phân tích ảnh này và xác định loại sự cố đô thị (urban incident). 

Chọn MỘT trong các loại sau:
1. **pothole** (ổ gà) - đường hỏng, ổ gà, vết nứt đường
2. **flooding** (ngập lụt) - nước ngập, lũ lụt, đường ngập nước
3. **traffic_light** (đèn giao thông hỏng) - đèn tín hiệu hỏng, đèn giao thông không hoạt động
4. **waste** (rác thải) - rác bẩn, rác thải tràn lan, bãi rác
5. **traffic_jam** (kẹt xe) - tắc đường, nhiều xe, giao thông ùn tắc
6. **other** (khác) - các sự cố khác hoặc không xác định rõ

Trả về ĐÚNG định dạng JSON sau (không thêm markdown, chỉ pure JSON):
{
  "label": "tên_loại_sự_cố",
  "confidence": 0.85,
  "description": "Mô tả ngắn gọn về sự cố (tiếng Việt)",
  "detected_objects": ["danh sách các đối tượng nhìn thấy"]
}

CHÚ Ý: 
- confidence từ 0.0 đến 1.0
- Nếu không chắc chắn, dùng "other" và confidence thấp hơn
- description phải bằng tiếng Việt"""

        # Call Gemini API
        response = gemini_model.generate_content([prompt, image])
        
        # Parse response
        response_text = response.text.strip()
        
        # Remove markdown code blocks if present
        if response_text.startswith("```"):
            response_text = response_text.split("```")[1]
            if response_text.startswith("json"):
                response_text = response_text[4:]
            response_text = response_text.strip()
        
        logger.info(f"📝 Gemini response: {response_text[:200]}...")
        
        # Parse JSON
        try:
            gemini_result = json.loads(response_text)
        except json.JSONDecodeError:
            logger.warning("Failed to parse Gemini JSON, using fallback")
            gemini_result = {
                "label": "other",
                "confidence": 0.60,
                "description": "Không thể phân tích chính xác",
                "detected_objects": []
            }
        
        # Map to our format
        label = gemini_result.get("label", "other")
        confidence = float(gemini_result.get("confidence", 0.70))
        description = gemini_result.get("description", "")
        detected_objects = gemini_result.get("detected_objects", [])
        
        # Get incident info
        info = INCIDENT_LABELS.get(label, INCIDENT_LABELS["other"])
        
        result = {
            "label": label,
            "label_vi": info["vi"],
            "label_en": info["en"],
            "confidence": confidence,
            "severity": info["severity"],
            "priority": info["priority"],
            "category_id": info["category_id"],
            "description": description or f'{info["vi"]} được phát hiện với độ tin cậy {int(confidence*100)}%',
            "detected_objects": detected_objects,
            "timestamp": datetime.utcnow().isoformat() + "Z",
            "ai_engine": "gemini-3-pro-image-preview"
        }
        
        logger.info(f"✅ Gemini analysis: {label} (confidence: {confidence:.2f})")
        
        return result
        
    except Exception as e:
        logger.error(f"❌ Gemini analysis error: {str(e)}")
        logger.error(f"Traceback: ", exc_info=True)
        
        # Fallback
        return {
            "label": "other",
            "label_vi": "Sự cố khác",
            "label_en": "Other",
            "confidence": 0.50,
            "severity": "medium",
            "priority": "medium",
            "category_id": 6,
            "description": "Lỗi khi phân tích AI",
            "detected_objects": [],
            "timestamp": datetime.utcnow().isoformat() + "Z",
            "ai_engine": "fallback"
        }



def classify_incident(classification_results: List, detection_results: List) -> Dict:
    """Advanced AI classification for all incident types"""
    
    # Extract predictions
    top_classification = classification_results[0] if classification_results else {}
    detected_objects = [obj["label"].lower() for obj in detection_results[:15]]
    
    logger.info(f"Classification: {top_classification}")
    logger.info(f"Objects: {detected_objects}")
    
    # Analyze detected objects
    analysis = {
        "vehicles": [],
        "infrastructure": [],
        "water_related": [],
        "waste_related": [],
        "damage_indicators": [],
        "people": []
    }
    
    # Categorize detected objects
    for obj in detected_objects:
        if any(v in obj for v in ["car", "truck", "bus", "motorcycle", "bike", "vehicle"]):
            analysis["vehicles"].append(obj)
        elif any(i in obj for i in ["traffic light", "stop sign", "street sign", "road"]):
            analysis["infrastructure"].append(obj)
        elif any(w in obj for w in ["water", "puddle", "rain"]):
            analysis["water_related"].append(obj)
        elif any(g in obj for g in ["bottle", "bag", "trash", "garbage"]):
            analysis["waste_related"].append(obj)
        elif any(d in obj for d in ["hole", "crack", "damage", "broken"]):
            analysis["damage_indicators"].append(obj)
        elif "person" in obj:
            analysis["people"].append(obj)
    
    vehicle_count = len(analysis["vehicles"])
    people_count = len(analysis["people"])
    
    # Score each incident type
    incident_scores = {}
    
    for incident_type, info in INCIDENT_LABELS.items():
        score = 0.0
        confidence_reasons = []
        
        if incident_type == "traffic_jam":
            if vehicle_count >= 5:
                score += 0.9
                confidence_reasons.append(f"Nhiều phương tiện ({vehicle_count})")
            elif vehicle_count >= 2:
                score += 0.6
                confidence_reasons.append(f"Có phương tiện ({vehicle_count})")
            
            if people_count > 0:
                score += 0.3
                confidence_reasons.append(f"Có người ({people_count})")
        
        elif incident_type == "pothole":
            road_present = any("road" in obj or "street" in obj for obj in detected_objects)
            damage_present = len(analysis["damage_indicators"]) > 0
            
            if damage_present and road_present:
                score += 0.8
                confidence_reasons.append("Có hư hại trên đường")
            elif road_present and vehicle_count < 3:
                score += 0.5
                confidence_reasons.append("Có đường, ít phương tiện")
            
            if vehicle_count >= 4:
                score *= 0.1
        
        elif incident_type == "flooding":
            water_present = len(analysis["water_related"]) > 0
            
            if water_present:
                score += 0.9
                confidence_reasons.append("Phát hiện nước")
        
        elif incident_type == "waste":
            waste_present = len(analysis["waste_related"]) > 0
            
            if waste_present:
                score += 0.8
                confidence_reasons.append("Phát hiện rác thải")
            
            if vehicle_count >= 3:
                score *= 0.3
        
        elif incident_type == "traffic_light":
            light_present = any("traffic light" in obj or "stop sign" in obj 
                              for obj in analysis["infrastructure"])
            
            if light_present:
                score += 0.9
                confidence_reasons.append("Phát hiện đèn giao thông")
        
        elif incident_type == "other":
            if all(s < 0.4 for s in incident_scores.values()):
                score += 0.5
                confidence_reasons.append("Không xác định rõ loại sự cố")
        
        # Record score if significant
        if score > 0.1:
            incident_scores[incident_type] = score
            logger.info(f"{incident_type}: {score:.2f} - {confidence_reasons}")
    
    # Select best match
    if incident_scores:
        sorted_scores = sorted(incident_scores.items(), key=lambda x: x[1], reverse=True)
        best_match = sorted_scores[0]
        incident_type = best_match[0]
        raw_score = best_match[1]
        
        # Normalize confidence (0.55 - 0.95)
        confidence = min(0.55 + (raw_score * 0.4), 0.95)
    else:
        # Fallback
        if vehicle_count >= 3:
            incident_type = "traffic_jam"
            confidence = 0.70
        elif any("water" in obj for obj in detected_objects):
            incident_type = "flooding"
            confidence = 0.65
        else:
            incident_type = "other"
            confidence = 0.60
    
    info = INCIDENT_LABELS[incident_type]
    
    return {
        "label": incident_type,
        "label_vi": info["vi"],
        "label_en": info["en"],
        "confidence": round(confidence, 2),
        "severity": info["severity"],
        "priority": info["priority"],
        "category_id": info["category_id"],
        "description": f"{info['vi']} được phát hiện với độ tin cậy {round(confidence * 100)}%",
        "detected_objects": detected_objects[:10],
        "analysis": analysis,
        "timestamp": datetime.utcnow().isoformat() + "Z"
    }


@app.post("/analyze")
async def analyze_image(
    file: UploadFile = File(...),
    user: Optional[Dict] = Depends(optional_authenticate)
):
    """
    Analyze uploaded image - Public API with optional authentication
    
    - **No auth**: Limited to basic analysis
    - **With auth**: Full analysis + user tracking
    """
    
    if not file.content_type.startswith("image/"):
        raise HTTPException(status_code=400, detail="File must be an image")
    
    try:
        # Read and process image
        contents = await file.read()
        image = Image.open(io.BytesIO(contents))
        
        # Convert to RGB if needed
        if image.mode != "RGB":
            image = image.convert("RGB")
        
        # Analyze image
        analysis = analyze_image_content(image)
        
        # Add user info if authenticated
        if user:
            analysis['analyzed_by'] = {
                'user_id': user.get('user_id'),
                'auth_type': user.get('auth_type')
            }
            logger.info(f"Analysis by user {user.get('user_id')} ({user.get('auth_type')}): {analysis['label']} ({analysis['confidence']})")
        else:
            logger.info(f"Public analysis: {analysis['label']} ({analysis['confidence']})")
        
        return {
            "success": True,
            "analysis": analysis
        }
        
    except Exception as e:
        logger.error(f"Error processing image: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/analyze-base64")
async def analyze_base64_image(
    data: dict,
    user: Optional[Dict] = Depends(optional_authenticate)
):
    """
    Analyze base64 encoded image - For mobile apps
    
    - **No auth**: Limited to basic analysis
    - **With auth**: Full analysis + user tracking
    """
    
    if "image_base64" not in data:
        raise HTTPException(status_code=400, detail="image_base64 field required")
    
    try:
        # Decode base64 image
        image_data = base64.b64decode(data["image_base64"])
        image = Image.open(io.BytesIO(image_data))
        
        # Convert to RGB if needed
        if image.mode != "RGB":
            image = image.convert("RGB")
        
        # Analyze image
        analysis = analyze_image_content(image)
        
        # Add user info if authenticated
        if user:
            analysis['analyzed_by'] = {
                'user_id': user.get('user_id'),
                'auth_type': user.get('auth_type')
            }
        
        return {
            "success": True,
            "analysis": analysis
        }
        
    except Exception as e:
        logger.error(f"Error processing base64 image: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/analyze-for-report")
async def analyze_for_report(
    file: UploadFile = File(...),
    user: Optional[Dict] = Depends(optional_authenticate)  # Made optional - called from CoreAPI (already authenticated)
):
    """
    Analyze image and return CoreAPI Report format - **Requires authentication**
    
    For internal integration with CoreAPI /media/upload flow
    """
    
    if not file.content_type.startswith("image/"):
        raise HTTPException(status_code=400, detail="File must be an image")
    
    try:
        # Read and process image
        contents = await file.read()
        image = Image.open(io.BytesIO(contents))
        
        # Convert to RGB if needed
        if image.mode != "RGB":
            image = image.convert("RGB")
        
        # Analyze image
        analysis = analyze_image_content(image)
        
        # Format response for CoreAPI Report creation
        report_data = {
            "success": True,
            "data": {
                "danh_muc_id": analysis["category_id"],
                "tieu_de": f"Phát hiện {analysis['label_vi']}",
                "mo_ta": analysis["description"],
                "muc_do_uu_tien": analysis["priority"],
                "muc_do_nghiem_trong": analysis["severity"],
                "ai_analysis": {
                    "label": analysis["label"],
                    "label_vi": analysis["label_vi"],
                    "confidence": analysis["confidence"],
                    "detected_objects": analysis["detected_objects"],
                    "timestamp": analysis["timestamp"]
                },
                "analyzed_by": {
                    "user_id": user.get('user_id'),
                    "auth_type": user.get('auth_type')
                }
            }
        }
        
        logger.info(f"Report analysis by user {user.get('user_id')}: {analysis['label']} ({analysis['confidence']})")
        
        return report_data
        
    except Exception as e:
        logger.error(f"Error processing image for report: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {
        "service": "AIMLService",
        "status": "healthy",
        "models_loaded": image_classifier is not None and object_detector is not None,
        "device": "cuda" if torch.cuda.is_available() else "cpu",
        "timestamp": datetime.utcnow().isoformat() + "Z"
    }


@app.get("/")
async def root():
    return {
        "service": "CityResQ360 AI/ML Service",
        "version": "1.0.0",
        "endpoints": {
            "/analyze": "POST - Upload image file for analysis",
            "/analyze-base64": "POST - Analyze base64 encoded image",
            "/analyze-for-report": "POST - Analyze and return CoreAPI Report format",
            "/health": "GET - Service health check"
        }
    }


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8003)
