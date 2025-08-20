#!/usr/bin/env python3
# Enhanced MedicalVoice Audio Processing Script
import assemblyai as aai
from openai import OpenAI
import json
import os
import sys
import traceback
import time
import hashlib
from pathlib import Path

# Enhanced API Keys with environment variable support
def get_api_keys():
    """Get API keys from environment variables or fallback to config"""
    assemblyai_key = os.getenv('ASSEMBLYAI_API_KEY', "35f010c057384f079f110265d2c6a164")
    openai_key = os.getenv('OPENAI_API_KEY', "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")
    return assemblyai_key, openai_key

# Initialize API clients
assemblyai_key, openai_key = get_api_keys()
aai.settings.api_key = assemblyai_key
client = OpenAI(api_key=openai_key)

# ✅ Validate arguments
if len(sys.argv) < 3:
    print(json.dumps({"error": "Audio file or log file path not provided"}))
    sys.exit(1)

audio_path = sys.argv[1]
log_file_path = sys.argv[2]

# ✅ Ensure log file directory exists
os.makedirs(os.path.dirname(log_file_path), exist_ok=True)

def log_info(message):
    """Write processing information to log file."""
    try:
        with open(log_file_path, "a", encoding="utf-8") as f:
            timestamp = time.strftime("%Y-%m-%d %H:%M:%S")
            f.write(f"[{timestamp}] {message}\n")
    except Exception:
        pass  # Silently continue if logging fails

def update_progress(stage, progress, message=""):
    """Update processing progress for real-time feedback"""
    progress_data = {
        "stage": stage,
        "progress": progress,
        "message": message,
        "timestamp": time.strftime("%Y-%m-%d %H:%M:%S")
    }
    print(json.dumps({"progress": progress_data}))

def get_file_info(audio_path):
    """Get audio file information"""
    try:
        file_size = os.path.getsize(audio_path)
        file_name = os.path.basename(audio_path)
        file_ext = os.path.splitext(file_name)[1].lower()
        
        # Calculate file hash for integrity
        with open(audio_path, 'rb') as f:
            file_hash = hashlib.md5(f.read()).hexdigest()
        
        return {
            "name": file_name,
            "size": file_size,
            "extension": file_ext,
            "hash": file_hash
        }
    except Exception:
        return None

def process_audio(audio_path):
    """Enhanced audio processing with progress tracking and better error handling."""
    try:
        update_progress("initialization", 0, "Starting audio processing...")
        log_info("Audio processing started")

        if not os.path.exists(audio_path):
            print(json.dumps({"error": "Audio file not found"}))
            return

        # Get file information
        update_progress("validation", 10, "Validating audio file...")
        file_info = get_file_info(audio_path)
        if not file_info:
            print(json.dumps({"error": "Failed to analyze audio file"}))
            return
        
        # Validate file format
        supported_formats = ['.mp3', '.m4a', '.wav', '.aac', '.flac', '.ogg']
        if file_info['extension'] not in supported_formats:
            print(json.dumps({"error": f"Unsupported audio format: {file_info['extension']}"}))
            return

        # Enhanced AssemblyAI Transcription
        update_progress("transcription", 20, "Starting speech-to-text conversion...")
        
        try:
            # Configure transcription settings for medical audio (RELIABLE configuration)
            config = aai.TranscriptionConfig(
                speaker_labels=False,  # FIXED: This was causing processing to hang
                punctuate=True,
                format_text=True
            )
            
            transcriber = aai.Transcriber(config=config)
            update_progress("transcription", 30, "Processing audio with AI...")
            
            transcript = transcriber.transcribe(audio_path)
            
            if not transcript or not transcript.text:
                print(json.dumps({"error": "Transcription failed - no text returned"}))
                return
            
            update_progress("transcription", 60, "Transcription completed successfully")
            log_info("Transcription completed successfully")
            
        except Exception as e:
            # FALLBACK: Try with even simpler configuration if first attempt fails
            try:
                log_info(f"Primary transcription failed, trying fallback: {str(e)}")
                update_progress("transcription", 25, "Trying fallback transcription method...")
                
                config = aai.TranscriptionConfig(
                    speaker_labels=False,
                    punctuate=False,  # Simplified fallback
                    format_text=False
                )
                
                transcriber = aai.Transcriber(config=config)
                transcript = transcriber.transcribe(audio_path)
                
                if not transcript or not transcript.text:
                    print(json.dumps({"error": "Both primary and fallback transcription failed"}))
                    return
                
                update_progress("transcription", 60, "Fallback transcription completed successfully")
                log_info("Fallback transcription completed successfully")
                
            except Exception as fallback_error:
                print(json.dumps({"error": f"Transcription failed (primary: {str(e)}, fallback: {str(fallback_error)})"}))
                return

        # Enhanced OpenAI Medical Analysis
        update_progress("analysis", 70, "Analyzing content with medical AI...")
        
        try:
            # Enhanced medical prompt for better analysis
            system_prompt = """You are an expert medical AI assistant specializing in clinical documentation and medical analysis. 

Your task is to analyze transcribed medical audio and provide:
1. CLINICAL SUMMARY: Key medical findings, symptoms, and observations
2. MEDICAL TERMINOLOGY: Identify and explain medical terms used
3. RECOMMENDATIONS: Suggest follow-up actions, tests, or treatments if applicable
4. RISK ASSESSMENT: Identify any potential concerns or red flags
5. STRUCTURED DATA: Extract vital signs, medications, diagnoses if mentioned

Format your response professionally for healthcare documentation. Be precise, evidence-based, and highlight critical information."""

            response = client.chat.completions.create(
                model="gpt-4",
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": f"Please analyze this medical transcription:\n\n{transcript.text}"}
                ],
                max_tokens=1500,
                temperature=0.2,
                presence_penalty=0.1,
                frequency_penalty=0.1
            )

            chatgpt_text = response.choices[0].message.content
            if not chatgpt_text:
                print(json.dumps({"error": "AI analysis failed - no response received"}))
                return
            
            update_progress("analysis", 90, "Medical analysis completed")
            log_info("Medical analysis completed successfully")
            
        except Exception as e:
            print(json.dumps({"error": f"AI analysis failed: {str(e)}"}))
            return

        # Enhanced JSON Output with Additional Data
        update_progress("saving", 95, "Saving results...")
        try:
            json_dir = os.path.join(os.path.dirname(audio_path), "../json")
            os.makedirs(json_dir, exist_ok=True)

            json_filename = os.path.splitext(os.path.basename(audio_path))[0] + ".json"
            json_path = os.path.abspath(os.path.join(json_dir, json_filename))

            # Enhanced output data with additional metadata
            output_data = {
                "transcribed_text": transcript.text,
                "medical_analysis": chatgpt_text,
                "chatgpt_response": chatgpt_text,  # Keep for backward compatibility
                "timestamp": time.strftime("%Y-%m-%d %H:%M:%S"),
                "audio_file": audio_path,
                "file_info": file_info,
                "processing_stats": {
                    "transcription_confidence": getattr(transcript, 'confidence', None),
                    "audio_duration": getattr(transcript, 'audio_duration', None),
                    "word_count": len(transcript.text.split()) if transcript.text else 0,
                    "processing_time": time.strftime("%Y-%m-%d %H:%M:%S")
                },
                "enhanced_features": {
                    "speaker_labels": False,  # FIXED: Always False for reliability
                    "punctuation": True,
                    "formatting": True
                },
                "version": "2.1",  # Updated version with speaker_labels fix
                "api_versions": {
                    "assemblyai": "enhanced",
                    "openai": "gpt-4"
                }
            }

            # Add AssemblyAI enhanced features if available (simplified)
            if hasattr(transcript, 'utterances') and transcript.utterances:
                output_data['speaker_info'] = [
                    {
                        "speaker": utterance.speaker,
                        "text": utterance.text,
                        "start": utterance.start,
                        "end": utterance.end
                    } for utterance in transcript.utterances[:10]  # Limit to first 10 for performance
                ]

            with open(json_path, "w", encoding="utf-8") as f:
                json.dump(output_data, f, indent=4, ensure_ascii=False)

            update_progress("completed", 100, "Processing completed successfully!")
            log_info("Results saved successfully")
            
            # Return success with enhanced metadata
            success_response = {
                "json_file": json_path,
                "status": "success",
                "enhanced": True,
                "processing_stats": output_data["processing_stats"],
                "file_info": file_info
            }
            print(json.dumps(success_response))
            
        except Exception as e:
            print(json.dumps({"error": f"Failed to save results: {str(e)}"}))
            return

    except Exception as e:
        log_info(f"Processing failed: {str(e)}")
        print(json.dumps({"error": f"Processing failed: {str(e)}"}))

if __name__ == "__main__":
    process_audio(audio_path)


