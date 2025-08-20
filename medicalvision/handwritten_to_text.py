import sys
import json
import os
from PIL import Image
import google.generativeai as genai

def main():
    try:
        if len(sys.argv) < 2:
            print(json.dumps({"error": "No image path provided"}, ensure_ascii=False), file=sys.stderr)
            sys.exit(1)
        
        image_path = sys.argv[1]
        
        # Check if image exists
        if not os.path.exists(image_path):
            print(json.dumps({"error": f"Image file not found: {image_path}"}, ensure_ascii=False), file=sys.stderr)
            sys.exit(1)
        
        # Open and process image
        image = Image.open(image_path)
        
        # Convert to RGB if necessary
        if image.mode != 'RGB':
            image = image.convert('RGB')
        
        # Configure Gemini AI
        api_key = "AIzaSyBpL9c4jjBmRnWviTVjDNTLjhAGmcqfUa4"
        genai.configure(api_key=api_key)
        
        # Create model
        model = genai.GenerativeModel('gemini-1.5-flash')
        
        # Prepare content
        content = [image, "Extract all handwritten text from this image. Provide a clean, readable transcription."]
        
        # Generate content
        response = model.generate_content(content)
        
        # Extract text
        extracted_text = response.text
        
        # Prepare output
        output = {
            "status": "success",
            "image_path": image_path,
            "handwritten_text": extracted_text,
            "error": None
        }
        
        # Output to stdout using binary write to avoid Unicode issues
        sys.stdout.buffer.write(json.dumps(output, ensure_ascii=False).encode('utf-8'))
        
        sys.exit(0)
        
    except Exception as e:
        error_output = {
            "error": f"Unexpected error: {str(e)}"
        }
        
        # Output error to stdout
        error_json = json.dumps(error_output, ensure_ascii=False)
        sys.stdout.buffer.write(error_json.encode('utf-8'))
        
        sys.exit(1)

if __name__ == "__main__":
    main()