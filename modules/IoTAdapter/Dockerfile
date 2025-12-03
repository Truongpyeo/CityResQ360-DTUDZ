FROM golang:1.21-alpine AS builder

WORKDIR /app

# Copy go mod files
COPY go.mod ./
COPY . .

# Download dependencies and build
RUN go mod tidy && go mod download
RUN go build -o main .

# Production image
FROM alpine:latest

RUN apk --no-cache add ca-certificates

WORKDIR /root/

COPY --from=builder /app/main .

EXPOSE 8011

CMD ["./main"]
