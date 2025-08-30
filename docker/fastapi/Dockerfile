FROM python:3.9-slim-buster

RUN pip install \
    fastapi \
    uvicorn \
    redis

WORKDIR /app

EXPOSE 8080

# dev mode
# CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8080", "--reload"]
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8080", "--workers", "32"]