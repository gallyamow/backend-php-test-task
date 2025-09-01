package main

import (
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/redis/go-redis/v9"
	"log"
	"net/http"
	"os"
	"strconv"
)

type Visit struct {
	CountryCode string `form:"countryCode"`
}

func main() {
	serverType := os.Getenv("SERVER_TYPE")
	redisHost := os.Getenv("REDIS_HOST")
	redisPort, _ := strconv.Atoi(os.Getenv("REDIS_PORT"))
	redisStorageKey := os.Getenv("REDIS_STORAGE_KEY")

	redisClient := redis.NewClient(&redis.Options{
		Addr: fmt.Sprintf("%s:%d", redisHost, redisPort),
	})

	router := gin.Default()

	router.GET("/", func(ctx *gin.Context) {
		ctx.JSON(http.StatusOK, map[string]string{
			"health": "good",
			"server": serverType,
		})
	})

	groupV1 := router.Group("/v1")

	groupV1.GET("/statistics", func(ctx *gin.Context) {
		hash := redisClient.HGetAll(ctx, redisStorageKey)
		if hash.Err() != nil {
			ctx.JSON(http.StatusInternalServerError, gin.H{"error": hash.Err()})
			return
		}

		items, err := hash.Result()
		if err != nil {
			ctx.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
			return
		}

		response := map[string]int{}
		for k, v := range items {
			i, err := strconv.Atoi(v)
			if err != nil {
				ctx.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
				return
			}
			response[k] = i
		}

		ctx.JSON(http.StatusOK, response)
	})

	groupV1.POST("/statistics", func(c *gin.Context) {
		var visit Visit
		if err := c.ShouldBind(&visit); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		redisClient.HIncrBy(c, redisStorageKey, visit.CountryCode, 1)

		c.Status(http.StatusCreated)
	})

	if err := router.Run(":8080"); err != nil {
		log.Fatal(err)
	}
}
