FROM node:20-alpine

ENV NODE_ENV=production \
    SERVICE_PORT=8001

WORKDIR /app

COPY package*.json ./

RUN if [ -f package-lock.json ]; then \
        npm ci --only=production; \
    else \
        npm install --omit=dev && npm cache clean --force; \
    fi

COPY . /app

EXPOSE 8001

CMD ["node", "src/server.js"]

