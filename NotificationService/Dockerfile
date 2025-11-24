FROM node:20-alpine

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN if [ -f package-lock.json ]; then \
        npm ci --only=production; \
    else \
        npm install --omit=dev && npm cache clean --force; \
    fi

# Copy application files
COPY . .

# Create uploads directory
RUN mkdir -p uploads

EXPOSE 8006

CMD ["node", "src/server.js"]
