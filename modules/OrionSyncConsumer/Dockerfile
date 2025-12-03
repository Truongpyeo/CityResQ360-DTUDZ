FROM golang:1.21-alpine AS builder

WORKDIR /app

COPY go.mod ./
# COPY go.sum ./
RUN go mod download

COPY . .
RUN go mod tidy

RUN go build -o orion-sync-consumer main.go

FROM alpine:latest

WORKDIR /root/

COPY --from=builder /app/orion-sync-consumer .

CMD ["./orion-sync-consumer"]
