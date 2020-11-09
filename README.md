# Mutant Checker

Mutant checker is an API that process DNA and decide if its mutant or human.

## Requirements

- [Docker](https://docs.docker.com/engine/install/) 19.03.
- [Docker-compose](https://docs.docker.com/compose/install/) 1.23.
- Create an acount in [DockerHub](https://hub.docker.com/)

## Deploy locally

Go to project root folder, open a terminal and run:
```bash
docker-compose -f docker-compose-local.yml up
```
This will start the services in the docker compose file throwing process logs to the terminal.

## Deploy on AWS
- Create a free [AWS account](https://aws.amazon.com/es/free/).
- [Create a Database](https://aws.amazon.com/es/getting-started/hands-on/create-mysql-db/) Instance on RDS, and name it mutants.
- In the docker-compose.yml file of project root folder replace the values of DB_HOST,DB_DATABASE, DB_USERNAME, DB_PASSWORD for the ones you have created in the previous step.
- Follow this [tutorial](https://docs.aws.amazon.com/AmazonECS/latest/developerguide/ecs-cli-tutorial-ec2.html) to create en EC2 cluster and deploy the app.

## Usage
#### POST Check if dna is mutant

```bash
api/mutant
```


```bash
curl -H 'Content-Type: application/json' --location --request POST 'localhost/api/mutant' \
--data-raw '{
"dna":["ATGCGA","CBGTGC","TTATGT","AGAAGG","CCBCTA","TCACTG"]
}'
```
#### Response Body

```bash
{
  "messages":['Error messages if exists']
}
```
#### Response HTTP Status Code


If status code is 200 the dna corresponds to a mutant.

If status code is 403 the dna corresponds to a human.

#### GET Gets statistics

```bash
api/stats
```

```bash
curl --location --request GET 'localhost/api/stats'
```

#### Response Body
```bash
{
    "count_mutant_dna": DNA mutant count on db(number),
    "count_human_dna": DNA human count on db(number),
    "ratio": ratio between 'count_mutant_dna' and 'count_human_dna'(float)
}
```

## License
MIT License

Copyright (c) [year] [fullname]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.