# Use an official Python image
FROM python:3.9-slim

# Set the working directory in the container
WORKDIR /app

# Copy all files from the current directory to /app in the container
COPY . .

# Install dependencies
RUN pip install --no-cache-dir -r requirements.txt

# Expose the port Flask will run on
EXPOSE 5000

# Run the Flask app
CMD ["python", "src/app.py"]
