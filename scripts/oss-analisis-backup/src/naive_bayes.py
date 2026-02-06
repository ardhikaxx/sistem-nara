import torch
import numpy as np
from collections import defaultdict
import pickle
from sklearn.feature_extraction.text import CountVectorizer
from sklearn.preprocessing import LabelEncoder
import warnings
warnings.filterwarnings('ignore')

class NaiveBayesClassifier:
    """Implementasi Naive Bayes Classifier dengan PyTorch"""
    
    def __init__(self, alpha=1.0):
        """
        Args:
            alpha: Parameter Laplace smoothing
        """
        self.alpha = alpha
        self.classes_ = None
        self.class_priors_ = None
        self.feature_probs_ = None
        self.vectorizer = None
        self.label_encoder = None
        self.vocab_size_ = None
        
    def fit(self, X, y):
        """
        Train Naive Bayes classifier
        
        Args:
            X: List of texts atau array-like
            y: List of labels
        """
        # Encode labels
        self.label_encoder = LabelEncoder()
        y_encoded = self.label_encoder.fit_transform(y)
        self.classes_ = self.label_encoder.classes_
        
        # Create vectorizer
        self.vectorizer = CountVectorizer(max_features=5000)
        X_vectorized = self.vectorizer.fit_transform(X).toarray()
        self.vocab_size_ = X_vectorized.shape[1]
        
        # Convert to PyTorch tensors
        X_tensor = torch.tensor(X_vectorized, dtype=torch.float32)
        y_tensor = torch.tensor(y_encoded, dtype=torch.long)
        
        # Calculate class priors
        self.class_priors_ = self._calculate_class_priors(y_tensor)
        
        # Calculate feature probabilities
        self.feature_probs_ = self._calculate_feature_probabilities(X_tensor, y_tensor)
        
        return self
    
    def _calculate_class_priors(self, y):
        """Menghitung prior probability untuk setiap kelas"""
        class_counts = torch.bincount(y)
        total_samples = len(y)
        priors = (class_counts + self.alpha) / (total_samples + self.alpha * len(class_counts))
        return priors.numpy()  # Convert to numpy array
    
    def _calculate_feature_probabilities(self, X, y):
        """Menghitung conditional probabilities P(feature|class)"""
        n_classes = len(torch.unique(y))
        n_features = X.shape[1]
        
        # Initialize probability matrix
        feature_probs = torch.zeros((n_classes, n_features))
        
        for class_idx in range(n_classes):
            # Get samples for this class
            class_mask = (y == class_idx)
            X_class = X[class_mask]
            
            # Sum of features for this class
            feature_sum = torch.sum(X_class, dim=0)
            total_words = torch.sum(feature_sum)
            
            # Apply Laplace smoothing
            smoothed_sum = feature_sum + self.alpha
            smoothed_total = total_words + self.alpha * self.vocab_size_
            
            # Calculate probabilities
            feature_probs[class_idx] = smoothed_sum / smoothed_total
        
        return feature_probs.numpy()  # Convert to numpy array
    
    def predict_proba(self, X):
        """
        Predict class probabilities
        
        Args:
            X: List of texts
        Returns:
            Probability matrix (n_samples x n_classes)
        """
        # Vectorize input
        X_vectorized = self.vectorizer.transform(X).toarray()
        X_tensor = torch.tensor(X_vectorized, dtype=torch.float32)
        
        n_samples = X_tensor.shape[0]
        n_classes = len(self.classes_)
        
        # Initialize probability matrix
        log_probs = torch.zeros((n_samples, n_classes))
        
        # Calculate log probabilities for each class
        for class_idx in range(n_classes):
            # Log prior
            log_probs[:, class_idx] = torch.log(torch.tensor(self.class_priors_[class_idx]))
            
            # Add log likelihood for each feature
            for i in range(n_samples):
                # Only consider features that appear in the document
                non_zero_indices = torch.nonzero(X_tensor[i] > 0).squeeze()
                if non_zero_indices.numel() > 0:
                    if non_zero_indices.dim() == 0:
                        non_zero_indices = non_zero_indices.unsqueeze(0)
                    
                    # Sum of log probabilities for features in document
                    feature_log_probs = torch.log(torch.tensor(self.feature_probs_[class_idx, non_zero_indices]))
                    log_probs[i, class_idx] += torch.sum(feature_log_probs * X_tensor[i, non_zero_indices])
        
        # Convert log probabilities to probabilities
        probs = torch.exp(log_probs - torch.max(log_probs, dim=1, keepdim=True)[0])
        probs = probs / torch.sum(probs, dim=1, keepdim=True)
        
        return probs.numpy()
    
    def predict(self, X):
        """
        Predict class labels
        
        Args:
            X: List of texts
        Returns:
            Predicted labels
        """
        probs = self.predict_proba(X)
        predicted_indices = np.argmax(probs, axis=1)
        return self.label_encoder.inverse_transform(predicted_indices)
    
    def predict_single(self, text):
        """Predict sentiment untuk satu teks"""
        probs = self.predict_proba([text])[0]
        predicted_idx = np.argmax(probs)
        predicted_label = self.label_encoder.inverse_transform([predicted_idx])[0]
        
        return {
            'text': text,
            'predicted_sentiment': predicted_label,
            'probabilities': {
                self.label_encoder.inverse_transform([i])[0]: float(probs[i]) 
                for i in range(len(self.classes_))
            }
        }
    
    def save(self, model_path, vectorizer_path=None, label_encoder_path=None):
        """Save model to disk"""
        model_data = {
            'alpha': float(self.alpha),
            'classes_': self.classes_.tolist() if hasattr(self.classes_, 'tolist') else list(self.classes_),
            'class_priors_': self.class_priors_.tolist() if hasattr(self.class_priors_, 'tolist') else list(self.class_priors_),
            'feature_probs_': self.feature_probs_.tolist() if hasattr(self.feature_probs_, 'tolist') else list(self.feature_probs_),
            'vocab_size_': int(self.vocab_size_)
        }
        
        with open(model_path, 'wb') as f:
            pickle.dump(model_data, f)
        
        if vectorizer_path:
            with open(vectorizer_path, 'wb') as f:
                pickle.dump(self.vectorizer, f)
        
        if label_encoder_path:
            with open(label_encoder_path, 'wb') as f:
                pickle.dump(self.label_encoder, f)
    
    def load(self, model_path, vectorizer_path=None, label_encoder_path=None):
        """Load model from disk"""
        with open(model_path, 'rb') as f:
            model_data = pickle.load(f)
        
        self.alpha = model_data['alpha']
        self.classes_ = np.array(model_data['classes_'])
        self.class_priors_ = np.array(model_data['class_priors_'])
        self.feature_probs_ = np.array(model_data['feature_probs_'])
        self.vocab_size_ = model_data['vocab_size_']
        
        if vectorizer_path:
            with open(vectorizer_path, 'rb') as f:
                self.vectorizer = pickle.load(f)
        
        if label_encoder_path:
            with open(label_encoder_path, 'rb') as f:
                self.label_encoder = pickle.load(f)
        
        return self
    
    def get_feature_importance(self, top_n=20):
        """Get most important features for each class"""
        if self.feature_probs_ is None:
            raise ValueError("Model belum dilatih")
        
        feature_names = self.vectorizer.get_feature_names_out()
        n_classes = len(self.classes_)
        
        feature_importance = {}
        
        for class_idx, class_name in enumerate(self.classes_):
            # Get probabilities for this class
            probs = self.feature_probs_[class_idx]
            
            # Get top N features
            top_indices = np.argsort(probs)[-top_n:][::-1]
            top_features = [
                (feature_names[idx], float(probs[idx])) 
                for idx in top_indices
            ]
            
            feature_importance[class_name] = top_features
        
        return feature_importance