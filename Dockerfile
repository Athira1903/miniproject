# Use a minimal Python image
FROM python:3.9-slim

# Set the working directory
WORKDIR /app

# Copy everything from the current directory to the container
COPY . .

# Install Python dependencies
RUN pip install --no-cache-dir -r requirements.txt

# Expose the Flask port
EXPOSE 5000

# Run the Flask app
CMD ["python", "src/app.py"]
