FROM node:20-alpine

ENV NODE_ENV=production \
    SERVICE_PORT=8002

WORKDIR /app

COPY package*.json ./

RUN if [ -f package-lock.json ]; then \
        npm ci --only=production; \
    else \
        npm install --omit=dev && npm cache clean --force; \
    fi

COPY . /app

EXPOSE 8002

CMD ["node", "src/server.js"]

