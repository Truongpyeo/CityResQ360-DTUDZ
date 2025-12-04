FROM golang:1.21-alpine AS builder

WORKDIR /app

COPY go.mod ./
# COPY go.sum ./
RUN go mod download

COPY . .
RUN go mod tidy

RUN go build -o analytics-consumer main.go

FROM alpine:latest

WORKDIR /root/

COPY --from=builder /app/analytics-consumer .

CMD ["./analytics-consumer"]
