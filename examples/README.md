# Examples

## Kafka Stack

+ [Confluent Plataform](https://docs.confluent.io/current/quickstart/ce-docker-quickstart.html#ce-docker-quickstart)
+ [Confluent Docker Github](https://github.com/confluentinc/cp-all-in-on)

```shell script
$ docker-compose up 
```

## Produce Messages

+ [Base64](https://www.base64encode.org/)

```text
POST /topics/simple-topic-example HTTP/1.1
Host: localhost:8082
Content-Type: application/json
Accept: application/vnd.kafka.v2+json, application/vnd.kafka+json, application/json

{
  "records": [
    {
      "key": "MTIz",
      "value": "aGVsbG8gS2Fma2E="
    }
  ]
}
```

```shell script
curl -X POST \
  http://localhost:8082/topics/simple-topic-example \
  -H 'accept: application/vnd.kafka.v2+json, application/vnd.kafka+json, application/json' \
  -H 'content-type: application/json' \
  -d '{
  "records": [
    {
      "key": "MTIz",
      "value": "aGVsbG8gS2Fma2E="
    }
  ]
}'
```
