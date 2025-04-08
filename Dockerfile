# Clone the GitHub repo
git clone https://github.com/Athira1903/miniproject.git

# Navigate into the project directory
cd miniproject

# Check the contents of the 'src' folder to make sure the Dockerfile exists
ls src

# Build the Docker image using the Dockerfile inside 'src'
docker build -f src/Dockerfile -t my-miniproject-image .
