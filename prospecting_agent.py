import os
import pandas as pd
import sys

# Attempt to import google generative AI; fallback to dummy generator
try:
    import google.generativeai as genai
    from typing import List
    API_AVAILABLE = True
except Exception:
    API_AVAILABLE = False

# ----------------------------------------------------------------------
# Configuration
# ----------------------------------------------------------------------
API_KEY = os.getenv("GOOGLE_GENAI_API_KEY")
MODEL_NAME = "gemini-2.5-flash"
SYSTEM_INSTRUCTION = (
    "You are an AI prospecting agent for Toko SS, a water gallon and box distributor. "
    "Create a short, friendly, and personalized WhatsApp outreach message (max 160 chars) for each lead. "
    "Emphasize that Toko SS is an emergency backup supplier, operates on a strict pick‑up model, "
    "and offers negotiable prices for cardboard boxes. Vary the wording and include a friendly greeting. "
    "Do NOT mention prices directly; focus on reliability, pick‑up convenience, and flexibility for bulk orders."
)

# ----------------------------------------------------------------------
# Helper Functions
# ----------------------------------------------------------------------
def load_leads(csv_path: str) -> pd.DataFrame:
    df = pd.read_csv(csv_path)
    required_cols = {"company", "contact_name", "phone", "rating", "review_count"}
    missing = required_cols - set(df.columns.str.lower())
    if missing:
        raise ValueError(f"Missing required columns in CSV: {missing}")
    df.columns = df.columns.str.lower()
    return df

def filter_leads(df: pd.DataFrame, min_rating: float = 4.0, min_reviews: int = 10) -> pd.DataFrame:
    return df[(df["rating"] >= min_rating) & (df["review_count"] >= min_reviews)].copy()

def dummy_message(row: pd.Series) -> str:
    return (f"Hai {row['contact_name']}, kami Toko SS siap jadi backup air minum anda! "
            f"Pick‑up mudah, box kardus fleksibel. Hubungi kami di {row['phone']}.")

def generate_message(row: pd.Series, model) -> str:
    if not API_AVAILABLE:
        return dummy_message(row)
    if not API_KEY:
        return dummy_message(row)
    prompt = (
        f"Lead details:\n"
        f"- Company: {row['company']}\n"
        f"- Contact: {row['contact_name']}\n"
        f"- Phone: {row['phone']}\n"
        f"Create a concise WhatsApp outreach message based on the system instruction."
    )
    try:
        response = model.generate_content(
            contents=prompt,
            generation_config=genai.types.GenerationConfig(max_output_tokens=200, temperature=0.8),
            system_instruction=SYSTEM_INSTRUCTION,
        )
        if response.candidates and response.candidates[0].content.parts:
            return response.candidates[0].content.parts[0].text.strip()
    except Exception as e:
        print(f"[GenAI error: {e}]", file=sys.stderr)
    return dummy_message(row)

def enrich_with_messages(df: pd.DataFrame) -> pd.DataFrame:
    if API_AVAILABLE and API_KEY:
        genai.configure(api_key=API_KEY)
        model = genai.GenerativeModel(MODEL_NAME)
    else:
        model = None
    messages = []
    for _, row in df.iterrows():
        messages.append(generate_message(row, model))
    df = df.copy()
    df["whatsapp_message"] = messages
    return df

def main():
    input_csv = "b2b_leads_tokoss.csv"
    output_csv = "b2b_hot_leads_tokoss.csv"
    if not os.path.exists(input_csv):
        print(f"Input file '{input_csv}' not found.", file=sys.stderr)
        sys.exit(1)
    leads = load_leads(input_csv)
    qualified = filter_leads(leads)
    enriched = enrich_with_messages(qualified)
    enriched.to_csv(output_csv, index=False)
    print(f"Enriched {len(enriched)} leads saved to {output_csv}")

if __name__ == "__main__":
    main()
