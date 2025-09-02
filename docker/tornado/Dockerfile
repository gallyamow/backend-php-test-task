FROM python:3.9-slim-buster

RUN pip install \
    tornado \
    redis

WORKDIR /app

EXPOSE 8080

CMD ["python", "main.py"]
