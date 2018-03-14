# Drupal GraphQL Mutation Example



CREATE ARTICLE MUTATION
```$xslt
mutation {
  createArticle(input:{title: "Hey", body:"Ho"}){
    errors,
    violations{
      message,
      path,
      code
    },
    entity {
      entityUuid
      ...on NodeArticle {
        nid
        title,
        body {
          value
          format
          processed
          summary
          summaryProcessed
        }
      }
    }
  }
}

```

RESULT

```$xslt
{
  "data": {
    "createArticle": {
      "errors": [],
      "violations": [],
      "entity": {
        "entityUuid": "f0628b9d-bb9b-452a-aeea-bfdc2000660e",
        "nid": 21,
        "title": "Hey",
        "body": {
          "value": "Ho",
          "format": "null",
          "processed": "<p>Ho</p>\n",
          "summary": "null",
          "summaryProcessed": ""
        }
      }
    }
  }
}
```


---


UPDATE MUTATION

```$xslt
mutation {
  updateArticle(id:"21", input:{title: "Heyo", body:"Let's go"}){
    errors,
    violations{
      message,
      path,
      code
    },
    entity {
      entityUuid
      ...on NodeArticle {
        nid
        title,
        body {
          value
          format
          processed
          summary
          summaryProcessed
        }
      }
    }
  }
}
```


RESULT
```$xslt
{
  "data": {
    "updateArticle": {
      "errors": [],
      "violations": [],
      "entity": {
        "entityUuid": "f0628b9d-bb9b-452a-aeea-bfdc2000660e",
        "nid": 21,
        "title": "Heyo",
        "body": {
          "value": "Let's go",
          "format": "null",
          "processed": "<p>Let&#039;s go</p>\n",
          "summary": "null",
          "summaryProcessed": ""
        }
      }
    }
  }
}
```


---


DELETE MUTATION
```$xslt
mutation {
  deleteArticle(id:21){
    errors,
    violations{
      message,
      path,
      code
    },
    entity {
      entityUuid
      ...on NodeArticle {
        nid
        title,
        body {
          value
          format
          processed
          summary
          summaryProcessed
        }
      }
    }
  }
}

```


RESULT

```$xslt
{
  "data": {
    "deleteArticle": {
      "errors": [],
      "violations": [],
      "entity": {
        "entityUuid": "f0628b9d-bb9b-452a-aeea-bfdc2000660e",
        "nid": 21,
        "title": "Heyo",
        "body": {
          "value": "Let's go",
          "format": "null",
          "processed": "<p>Let&#039;s go</p>\n",
          "summary": "null",
          "summaryProcessed": ""
        }
      }
    }
  }
}
```