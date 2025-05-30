# train_model.py
import pandas as pd
from sklearn.linear_model import LogisticRegression
from sklearn.model_selection import train_test_split
import joblib
import os

# Load CSV
df = pd.read_csv('data/attendance.csv')

# Feature Engineering
df['attendance_percent'] = df['attended_classes'] / df['total_classes']
df['avg_per_week'] = df['attended_classes'] / df['weeks_passed']

# Target: Safe (1) if >= 75%, else Risk (0)
df['label'] = (df['attendance_percent'] >= 0.75).astype(int)

# Features
X = df[['total_classes', 'attended_classes', 'weeks_passed', 'attendance_percent', 'avg_per_week']]
y = df['label']

# Train Model
model = LogisticRegression()
model.fit(X, y)

# Save model
os.makedirs('model', exist_ok=True)
joblib.dump(model, 'model/predictor.pkl')
print("✅ Model trained and saved to model/predictor.pkl")
