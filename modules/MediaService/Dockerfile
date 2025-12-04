FROM node:20-alpine

WORKDIR /app

# Install runtime dependencies for Sharp
RUN apk add --no-cache vips-cpp

# Install build dependencies
RUN apk add --no-cache --virtual .build-deps \
    python3 \
    make \
    g++ \
    vips-dev

# Install dependencies
COPY package*.json ./
RUN npm install --omit=dev

# Remove build dependencies to keep image small
RUN apk del .build-deps

# Copy source code
COPY . .

# Create uploads directory
RUN mkdir -p uploads

EXPOSE 8004

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD node -e "require('http').get('http://localhost:8004/health', (r) => {process.exit(r.statusCode === 200 ? 0 : 1)})"

CMD ["node", "/app/src/server.js"]
