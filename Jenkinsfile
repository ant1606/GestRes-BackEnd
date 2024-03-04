pipeline {
  agent any
  environment {
      HOST_PATH = '/home/laptop/DesarrolloSoftware/ci_cd/jenkins/jenkins-data'
      PRODUCTION_PATH = '/home/proyectosDeploy'
      WORKSPACE_PATH = sh(script: "echo ${WORKSPACE} | sed 's@/var/jenkins_home@${HOST_PATH}@g'", returnStdout: true).trim()
      IMAGE_PRODUCTION = 'pygestorrecursos_backend:php_8.1-apache'
      CONTAINER_PRODUCTION = 'prodlocal_pygestorrecursos_backend'
      IMAGE_BUILD = 'pygestorrecursos-api:dev'
      CONTAINER_BUILD = 'pygestorrecursos-api-dev-jenkins'
      SERVICE_NAME_PRODUCTION = 'prodlocal_pygestorrecursos_backend'
  }
  stages {
    stage('Deteniendo y eliminando container api backend en producción') {
      when {
        expression { 
          CONTAINER_EXIST = sh(returnStdout: true, script: "docker ps -q --filter name=${CONTAINER_PRODUCTION}").trim()
          return CONTAINER_EXIST != '' 
        }
      }
      steps {
        script {
          sh """
            docker stop ${CONTAINER_PRODUCTION}
            docker rm ${CONTAINER_PRODUCTION}
          """
        }
      }
    }
    stage('Limpiando archivos del repositorio'){
      steps{
        script{
          sh """
          find . \\( -type f \\( -name ".dockerignore" -o -name ".editorconfig" -o -name ".env.example" -o -name "Dockerfile" -o -name "composer.lock" -o -name "docker-compose.prod.yml" -o -name "docker-compose.yml"\\) -o -type d \\( -name "Dockerfiles" \\) \\) -print0 | xargs -0 rm -rf
          """
        }
      }
    }
    stage('Copiando archivo .env y docker-compose') {
      steps {
        script {
          sh """
            cp /home/scm/gestorRecursos/frontend/.env .env
            cp /home/scm/gestorRecursos/frontend/docker-compose.yml docker-compose.yml
          """
        }
      }
    }
    stage('Borrando código fuentes de carpeta en producción'){
      steps{
        script{
          sh """
            cd ${PRODUCTION_PATH}/gestorRecursos/backend/
            find . -type d \\( -name "storage" -o -name "vendor" \\) -prune -o -print0 | xargs -0 rm -rf
          """
        }
      }
    }
    stage('Zipeando codigo fuente '){
      steps{
        script{
          sh """
            zip -r code_source.zip . -x "storage/*" "tests/*"
          """
        }
      }
    }
    stage('Copiando y descomprimiendo archivos fuentes a carpeta de producción'){
      steps{
        script{
          sh """
            cp code_source.zip ${PRODUCTION_PATH}/gestorRecursos/backend/code_source.zip
            rm code_source.zip
            cd ${PRODUCTION_PATH}/gestorRecursos/backend/
            unzip code_source.zip
            rm code_source.zip
          """
        }
      }
    }
    stage('Ejecutar Docker Compose en entorno de producción') {
      steps {
        script {
          sh """
            cd ${PRODUCTION_PATH}/gestorRecursos && docker compose -f "docker-compose.yml" up -d --build ${SERVICE_NAME_PRODUCTION}
          """
        }
      }
    }
  }
}