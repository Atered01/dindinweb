# ollama_client.py
import requests

def chamar_ollama(prompt, model="mistral:instruct"):
    url = "http://localhost:11434/api/generate"
    response = requests.post(url, json={
        "model": model,
        "prompt": prompt,
        "stream": False
    })
    response.raise_for_status()
    return response.json().get("response", "")
