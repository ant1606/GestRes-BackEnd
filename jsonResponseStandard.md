# Respuestas Satisfactoria 
## Con una sola entidad
    {
      "status": "success",
      "code": 200,
      "data": {
        "user": {
          "id": 123,
          "username": "john_doe",
          "email": "john@example.com"
        }
      }
    }

## Con varias entidades
    {
      "status": "success",
      "code": 200,
      "data": {
        "user": [
          {
            "id": 123,
            "username": "john_doe",
            "email": "john@example.com"
          },
          {
            "id": 123,
            "username": "john_doe",
            "email": "john@example.com"
          },
        ]
      }
    }

## Con respuesta Paginada
    {
      "status": "success",
      "code": 200,
      "data": {
        "user": [
          {
            "id": 123,
            "username": "john_doe",
            "email": "john@example.com"
          },
          {
            "id": 123,
            "username": "john_doe",
            "email": "john@example.com"
          },
        ]
      }
      "meta" : {
        "path" : "http://localhost/api/v1/tag?perPage=5&page=1",
        "currentPage" : 1,
        "perPage" : 5,
        "totalPages" : 4,
        "from" : 1,
        "to" : 5,
        "total" : 20,
      },
      "links" : {
        "self"  : "http://localhost/api/v1/tag?perPage=5&page=1",
        "first" : "http://localhost/api/v1/tag?perPage=5&page=1",
        "last"  : "http://localhost/api/v1/tag?perPage=5&page=4",
        "next"  : "http://localhost/api/v1/tag?perPage=5&page=3",
        "prev"  : null,
      },
    }

## Con entidad vacia
    {
      "status": "success",
      "code": 200,
      "data": {
        "user": []
      }
      "message": "No se encontraron resultados",
    }

# Respuesta con Error
    {
      "status": "error",
      "code": 404,
      "error": { 
        "message": "User not found",
        "details": [
            "inputName": "The requested user with ID 123 was not found.",
            "inputEmail" : "The requested email doesnt have valid format",
          ]
      }
    }

# LogIn
    {
      "status": "success",
      "code": 200,
      "bearer_token": "39|fTEPWXnKdGhIHw2BonIi6oXd6Cf8jBI3KDZ5AzvGea9f34c3",
      "bearer_expire": "Fri, 26 Jan 2024 05:06:20 GMT",
      "user": {
        "id": 1,
        "name": "Test User",
        "email": "test@example.com",
        "remember_token": null,
        "is_verified": true
      }
    }


# LogOut

# Objetos Custom
    {
      "status": "success",
      "code": 200,
      "data": {
        "id": 1,
        "name": "Test User",
      }
    }
- Queda pendiente establecer cuando se necesite enviar solo un mensaje sin ninguna entidad
- Queda pendiente determinar el objeto de respuesta a LogIn y LogOut
- Queda pendiente determinar como enviar objetos custom 
