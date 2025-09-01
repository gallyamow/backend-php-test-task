FROM golang:1.24-alpine

WORKDIR /app

# can't use volumes because of build time
COPY go.mod go.sum ./
RUN go mod download

COPY src/gin-main.go ./main.go

RUN CGO_ENABLED=0 GOOS=linux go build -o main .

EXPOSE 8080

CMD ["./main"]