# Use official Python image
FROM python:3.10-slim

# Set working directory
WORKDIR /app

# Copy everything first (including requirements.txt if it exists)
COPY . .

# Install dependencies if requirements.txt exists
RUN if [ -f "requirements.txt" ]; then \
        pip install --no-cache-dir -r requirements.txt ; \
    else \
        echo "No requirements.txt found. Skipping pip install." ; \
    fi

# Run the application
CMD ["python", "app.py"]
