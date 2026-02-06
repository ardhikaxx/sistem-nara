import pandas as pd
import numpy as np
from src.naive_bayes import NaiveBayesClassifier
from src.preprocess import TextPreprocessor
from config import (
    MODEL_PATH, VECTORIZER_PATH, LABEL_ENCODER_PATH,
    PREPROCESS_CONFIG
)
import warnings
warnings.filterwarnings('ignore')

class SentimentPredictor:
    """Class untuk memprediksi sentimen teks baru"""
    
    def __init__(self):
        """Initialize predictor with trained model"""
        print("Memuat model...")
        self.model = NaiveBayesClassifier()
        self.model.load(
            str(MODEL_PATH),
            str(VECTORIZER_PATH),
            str(LABEL_ENCODER_PATH)
        )
        
        # Initialize preprocessor
        self.preprocessor = TextPreprocessor(PREPROCESS_CONFIG)
        
        print(f"Model loaded successfully!")
        print(f"Classes: {list(self.model.classes_)}")
    
    def predict_text(self, text):
        """Predict sentiment for a single text"""
        # Preprocess text
        processed_text = self.preprocessor.preprocess_text(text)
        
        # Predict
        result = self.model.predict_single(processed_text)
        
        return {
            'original_text': text,
            'processed_text': processed_text,
            'sentiment': result['predicted_sentiment'],
            'confidence': max(result['probabilities'].values()),
            'probabilities': result['probabilities']
        }
    
    def predict_batch(self, texts):
        """Predict sentiment for multiple texts"""
        processed_texts = [
            self.preprocessor.preprocess_text(text) 
            for text in texts
        ]
        
        predictions = self.model.predict(processed_texts)
        probabilities = self.model.predict_proba(processed_texts)
        
        results = []
        for i, (text, pred, probs) in enumerate(zip(texts, predictions, probabilities)):
            results.append({
                'text': text,
                'processed_text': processed_texts[i],
                'sentiment': pred,
                'confidence': probs[np.argmax(probs)],
                'probabilities': {
                    self.model.classes_[j]: float(probs[j]) 
                    for j in range(len(self.model.classes_))
                }
            })
        
        return results
    
    def analyze_sentiment_distribution(self, texts):
        """Analyze sentiment distribution for multiple texts"""
        results = self.predict_batch(texts)
        
        sentiments = [r['sentiment'] for r in results]
        sentiment_counts = pd.Series(sentiments).value_counts()
        
        analysis = {
            'total_texts': len(texts),
            'sentiment_counts': sentiment_counts.to_dict(),
            'sentiment_percentages': {
                sentiment: (count / len(texts)) * 100 
                for sentiment, count in sentiment_counts.items()
            },
            'results': results
        }
        
        return analysis

def predict_example():
    """Contoh penggunaan predictor"""
    predictor = SentimentPredictor()
    
    # Contoh teks untuk diprediksi
    example_texts = [
        "Aplikasi sangat bagus dan mudah digunakan",
        "Tidak bisa login, error terus",
        "Cukup membantu untuk urusan perizinan",
        "Sangat buruk, tidak recommended",
        "Fitur lengkap tapi agak lambat"
    ]
    
    print("\n" + "=" * 60)
    print("CONTOH PREDIKSI SENTIMEN")
    print("=" * 60)
    
    for i, text in enumerate(example_texts, 1):
        result = predictor.predict_text(text)
        
        print(f"\nContoh {i}:")
        print(f"  Teks: {text[:50]}..." if len(text) > 50 else f"  Teks: {text}")
        print(f"  Sentimen: {result['sentiment'].upper()}")
        print(f"  Confidence: {result['confidence']:.2%}")
        print(f"  Probabilitas: {result['probabilities']}")
    
    # Analisis batch
    print("\n" + "=" * 60)
    print("ANALISIS BATCH")
    print("=" * 60)
    
    analysis = predictor.analyze_sentiment_distribution(example_texts)
    
    print(f"\nTotal teks: {analysis['total_texts']}")
    print("\nDistribusi Sentimen:")
    for sentiment, count in analysis['sentiment_counts'].items():
        percentage = analysis['sentiment_percentages'][sentiment]
        print(f"  {sentiment}: {count} ({percentage:.1f}%)")

def predict_from_csv(csv_path, text_column='review_content'):
    """Predict sentiments from CSV file"""
    predictor = SentimentPredictor()
    
    print(f"\nMemuat data dari: {csv_path}")
    df = pd.read_csv(csv_path)
    
    if text_column not in df.columns:
        raise ValueError(f"Kolom '{text_column}' tidak ditemukan")
    
    texts = df[text_column].dropna().tolist()
    
    print(f"Memproses {len(texts)} teks...")
    results = predictor.predict_batch(texts[:100])  # Limit to 100 for demo
    
    # Add predictions to dataframe
    sentiments = [r['sentiment'] for r in results]
    df_pred = df.head(len(results)).copy()
    df_pred['predicted_sentiment'] = sentiments
    
    # Save results
    output_path = 'data/predictions.csv'
    df_pred.to_csv(output_path, index=False)
    
    print(f"\nHasil prediksi disimpan di: {output_path}")
    
    # Show summary
    sentiment_counts = pd.Series(sentiments).value_counts()
    print("\nDistribusi Sentimen Prediksi:")
    for sentiment, count in sentiment_counts.items():
        percentage = (count / len(sentiments)) * 100
        print(f"  {sentiment}: {count} ({percentage:.1f}%)")
    
    return df_pred

if __name__ == "__main__":
    # Contoh penggunaan
    print("SENTIMENT ANALYSIS PREDICTOR")
    print("-" * 40)
    
    # Pilih mode
    print("\nPilih mode:")
    print("1. Contoh prediksi")
    print("2. Prediksi dari CSV")
    
    choice = input("\nMasukkan pilihan (1 atau 2): ").strip()
    
    if choice == "1":
        predict_example()
    elif choice == "2":
        csv_path = input("Masukkan path ke file CSV: ").strip()
        text_column = input("Masukkan nama kolom teks (default: 'review_content'): ").strip() or 'review_content'
        
        try:
            predict_from_csv(csv_path, text_column)
        except Exception as e:
            print(f"Error: {e}")
    else:
        print("Pilihan tidak valid")