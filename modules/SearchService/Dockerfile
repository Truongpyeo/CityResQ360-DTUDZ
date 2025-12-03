FROM golang:1.21-alpine AS builder

WORKDIR /app

# Copy everything first
COPY . .

# Download dependencies and generate go.sum
RUN go mod tidy && go mod download

# Build
RUN go build -o main .

# Production image
FROM alpine:latest
RUN apk --no-cache add ca-certificates

WORKDIR /root/

COPY --from=builder /app/main .

EXPOSE 8007

CMD ["./main"]
