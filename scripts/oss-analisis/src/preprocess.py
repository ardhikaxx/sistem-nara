import re
import string
import pandas as pd
import numpy as np
from nltk.corpus import stopwords
from nltk.tokenize import word_tokenize
from nltk.stem import PorterStemmer
import nltk
from tqdm import tqdm
import warnings
warnings.filterwarnings('ignore')

# Download NLTK resources
try:
    nltk.data.find('tokenizers/punkt')
except LookupError:
    nltk.download('punkt')
try:
    nltk.data.find('corpora/stopwords')
except LookupError:
    nltk.download('stopwords')

class TextPreprocessor:
    """Preprocessor untuk teks bahasa Indonesia"""
    
    def __init__(self, config):
        self.config = config
        self.stopwords_id = set(stopwords.words('indonesian'))
        self.stemmer = PorterStemmer()
        
    def clean_text(self, text):
        """Membersihkan teks"""
        if not isinstance(text, str):
            return ""
        
        # Convert to lowercase
        text = text.lower()
        
        # Remove URLs
        text = re.sub(r'https?://\S+|www\.\S+', '', text)
        
        # Remove mentions and hashtags
        text = re.sub(r'@\w+|#\w+', '', text)
        
        # Remove numbers
        text = re.sub(r'\d+', '', text)
        
        # Remove punctuation if configured
        if self.config['remove_punct']:
            text = text.translate(str.maketrans('', '', string.punctuation))
        
        # Remove extra whitespace
        text = re.sub(r'\s+', ' ', text).strip()
        
        return text
    
    def remove_stopwords(self, tokens):
        """Menghapus stopwords"""
        if not self.config['remove_stopwords']:
            return tokens
        return [word for word in tokens if word not in self.stopwords_id]
    
    def apply_stemming(self, tokens):
        """Menerapkan stemming"""
        if not self.config['stemming']:
            return tokens
        return [self.stemmer.stem(word) for word in tokens]
    
    def filter_by_length(self, tokens):
        """Filter kata berdasarkan panjang"""
        min_len = self.config['min_word_length']
        return [word for word in tokens if len(word) >= min_len]
    
    def preprocess_text(self, text):
        """Pipeline preprocessing untuk satu teks"""
        # Clean text
        text = self.clean_text(text)
        
        # Tokenize
        tokens = word_tokenize(text)
        
        # Remove stopwords
        tokens = self.remove_stopwords(tokens)
        
        # Apply stemming
        tokens = self.apply_stemming(tokens)
        
        # Filter by length
        tokens = self.filter_by_length(tokens)
        
        return ' '.join(tokens)
    
    def preprocess_dataframe(self, df, text_column='review_content', show_progress=True):
        """Preprocessing untuk seluruh dataframe"""
        print("Memulai preprocessing teks...")
        
        # Preprocess teks
        texts = df[text_column].tolist()
        processed_texts = []
        
        iter_texts = tqdm(texts, desc="Preprocessing") if show_progress else texts
        
        for text in iter_texts:
            processed_texts.append(self.preprocess_text(text))
        
        df['processed_text'] = processed_texts
        
        # Hapus baris dengan teks kosong setelah preprocessing
        initial_count = len(df)
        df = df[df['processed_text'].str.len() > 0].copy()
        removed_count = initial_count - len(df)
        
        print(f"\nPreprocessing selesai!")
        print(f"Data awal: {initial_count} baris")
        print(f"Data setelah preprocessing: {len(df)} baris")
        print(f"Dihapus: {removed_count} baris (teks kosong)")
        
        return df

def prepare_data_for_analysis(raw_data_path, config):
    """Mempersiapkan data untuk analisis sentimen"""
    
    print("Membaca data...")
    df = pd.read_csv(raw_data_path)
    
    # Validasi kolom yang diperlukan
    required_columns = ['review_content', 'rating']
    missing_columns = [col for col in required_columns if col not in df.columns]
    
    if missing_columns:
        raise ValueError(f"Kolom berikut tidak ditemukan: {missing_columns}")
    
    print(f"Data awal: {len(df)} baris")
    print(f"Kolom yang tersedia: {list(df.columns)}")
    
    # Tambahkan label sentimen berdasarkan rating
    print("\nMenambahkan label sentimen berdasarkan rating...")
    df['sentiment_label'] = df['rating'].map(config['RATING_TO_SENTIMENT'])
    df['sentiment'] = df['sentiment_label'].map(config['SENTIMENT_LABELS'])
    
    # Hitung distribusi sentimen
    sentiment_counts = df['sentiment_label'].value_counts()
    print("\nDistribusi Sentimen Awal:")
    for label, count in sentiment_counts.items():
        percentage = (count / len(df)) * 100
        print(f"  {label}: {count} ({percentage:.1f}%)")
    
    # Preprocessing teks
    preprocessor = TextPreprocessor(config['PREPROCESS_CONFIG'])
    df = preprocessor.preprocess_dataframe(df)
    
    return df

if __name__ == "__main__":
    from config import PREPROCESS_CONFIG, RATING_TO_SENTIMENT, SENTIMENT_LABELS
    
    config = {
        'PREPROCESS_CONFIG': PREPROCESS_CONFIG,
        'RATING_TO_SENTIMENT': RATING_TO_SENTIMENT,
        'SENTIMENT_LABELS': SENTIMENT_LABELS
    }
    
    # Contoh penggunaan
    df = prepare_data_for_analysis('data/reviews.csv', config)
    df.to_csv('data/processed_reviews.csv', index=False)