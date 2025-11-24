FROM node:20-alpine

ENV NODE_ENV=production \
    SERVICE_PORT=8001

WORKDIR /app

COPY . /app

RUN if [ -f package.json ]; then \
        npm install --production && npm cache clean --force; \
    fi

EXPOSE 8001

CMD ["sh", "-c", "if [ -f server.js ]; then node server.js; else tail -f /dev/null; fi"]

