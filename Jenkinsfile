pipeline {
    agent any

    environment {
        APP_ENV = 'prod'
    }

    stages {
        stage('Composer') {
            steps {
                sh 'composer install --no-dev --optimize-autoloader'
                sh 'php bin/console cache:clear'
            }
        }
        stage('Yarn') {
            steps {
                sh 'yarn install'
                sh 'yarn encore production'
            }
        }
        // TODO: add test stage
        stage('Zip artifact') {
            steps {
                script {
                    zip archive: true, dir: '', glob: '', zipFile: 'production.zip'
                }
            }
        }
    }
}