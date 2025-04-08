# Clone the project
git clone https://github.com/Athira1903/miniproject.git

# Move into the project directory
cd miniproject

# Check if Dockerfile exists in the 'src' folder
ls src
# You should see: Dockerfile

# Build the Docker image correctly
docker build -f src/Dockerfile -t miniproject-image .
