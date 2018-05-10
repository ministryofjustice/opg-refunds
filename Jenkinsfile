#!/usr/bin/env groovy

def start = new Date()
def dateString = (new Date()).format('YYYY-MM-dd')
def githubCredentialsId = '8e7238c5-c4d8-4f6c-80c1-995c78933ded'
def githubUserPassCredentialsId = '9fb2f9f6-657a-463c-bc1d-2da04b886e41'
def jobInfoShort = "${env.JOB_NAME} ${env.BUILD_DISPLAY_NAME}"
def failureMessage = ''
def hasFailed = false
def slackContent = ''
def colorCode = ''
def repoName = 'opg-refunds'
def slackColorMap = ['SUCCESS': 'good', 'FAILURE': 'danger', 'UNSTABLE': 'danger', 'ABORTED': 'danger']

// @todo - load this pipeline file, make sure it can populate gloval vars from this Jenkinsfile
// pipelineLib = load "ci/groovy"

// @todo - call this and see its output
// Fetching change set from Git

// @todo - re call timeDiff now that `time` evaluates in the script block
@NonCPS
def getChangeSet() {
  return currentBuild.changeSets.collect { cs ->
    cs.collect { entry ->
        "* ${entry.author.fullName}: ${entry.msg}"
    }.join("\n")
  }.join("\n")
}

def timeDiff(st) {
    def delta = (new Date()).getTime() - st.getTime()
    def seconds = delta.intdiv(1000) % 60
    def minutes = delta.intdiv(60 * 1000) % 60

    return "${minutes} min ${seconds} sec"
}

@NonCPS
def isPublishingBranch = { ->
    return env.GIT_BRANCH == 'origin/master'
}

// @NonCPS
// def getSlaveHostname = {
//   return InetAddress.localHost.canonicalHostName
// }

@NonCPS
def getGitAuthor = {
    def commit = sh(returnStdout: true, script: 'git rev-parse HEAD')
    author = sh(returnStdout: true, script: "git --no-pager show -s --format='%an' ${commit}").trim()
    return author
}

@NonCPS
def getLastCommitMessage() {
    return sh(returnStdout: true, script: 'git log -1 --pretty=%B').trim()
}

@NonCPS
def getCommitOwner() {
  if(env.CHANGE_AUTHOR_DISPLAY_NAME != null) {
    return env.CHANGE_AUTHOR_DISPLAY_NAME
  }
  return (sh(returnStdout: true, script: 'git show --no-patch --format="%an" HEAD')).trim()
}

def getCommitHash() {
  return sh(returnStdout: true, script: 'git rev-parse HEAD').trim()
}

def getCommitText() {
  return sh(returnStdout: true, script: 'git show -s --format=format:"*%s*  _by %an_" HEAD').trim()
}

def getGitHubBranchUrl() {

    if(env.CHANGE_URL != null) {
      return env.CHANGE_URL;
    }

    def githubRepo = 'https://github.com/ministryofjustice/opg-refunds/'
    def githubBranchUrl = githubRepo + 'tree/' + getRefundsBranchName()
    return githubBranchUrl;
}

// Because we can build both branches and pull requests, we don't want the release tag to container PR-95
// as it's not descriptive, instead we want the originating branch name i.e: SW-50. env.CHANGE_BRANCH contains this value if it's a PR being built.
def getRefundsBranchName() {
    if(env.CHANGE_BRANCH != null) {
        return env.CHANGE_BRANCH
    }
    return env.BRANCH_NAME
}

def getBaseSlackContent(currentResult) {
    // Slave: ${getSlaveHostname()}
    def blueOceanUrl = env.RUN_DISPLAY_URL
    def slackContent = """BUILD ${currentResult}
Branch: <${getGitHubBranchUrl()}|${getRefundsBranchName()}>
Build Number: <${env.BUILD_URL}|${env.BUILD_NUMBER}>
Urls: <${env.BUILD_URL}|Jenkins Classic> || <${blueOceanUrl}|Blue Ocean>
Commit Author: ${getCommitOwner()}
Commit Message: ${getLastCommitMessage()}
"""
    return slackContent
}

// @todo - consider php parallel list for 3 PHP components
// sh './vendor/bin/parallel-lint -s --exclude vendor/ .'

// END OF changeLogs()

pipeline {

  agent { label 'opg_sirius_slave' } // run on slaves only
  // agent { label 'paul_slave' } // run on slaves only

  environment {
    DOCKER_REGISTRY = 'registry.service.opg.digital'
    IS_CI = "true"

    CI_WORKSPACE_DIR = "${env.WORKSPACE}/ci"
    PUBLIC_FRONT_WORKSPACE_DIR = "${env.WORKSPACE}/public-front"
    CASEWORKER_FRONT_WORKSPACE_DIR = "${env.WORKSPACE}/caseworker-front"
    CASEWORKER_API_WORKSPACE_DIR = "${env.WORKSPACE}/caseworker-api"

    PUBLIC_FRONT_IMAGE = 'opg-refunds-public-front'
    CASEWORKER_FRONT_IMAGE = 'opg-refunds-caseworker-front'
    CASEWORKER_API_IMAGE = 'opg-refunds-caseworker-api'
    PHPCS_IMAGE = "${DOCKER_REGISTRY}/opguk/phpcs"
    NODE_RUNNER_IMAGE = 'opguk/node-runner'
    NODE_RUNNER_TAG = "latest"

    REFUNDS_NEW_TAG = "${getRefundsBranchName()}__${dateString}__${env.BUILD_NUMBER}"

    PUBLIC_FRONT_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.PUBLIC_FRONT_IMAGE}:${env.REFUNDS_NEW_TAG}"
    CASEWORKER_FRONT_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.CASEWORKER_FRONT_IMAGE}:${env.REFUNDS_NEW_TAG}"
    CASEWORKER_API_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.CASEWORKER_API_IMAGE}:${env.REFUNDS_NEW_TAG}"
    NODE_RUNNER_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.NODE_RUNNER_IMAGE}:${env.NODE_RUNNER_TAG}"

    PUBLIC_FRONT_IMAGE_VERSION = "${env.REFUNDS_NEW_TAG}"
    CASEWORKER_FRONT_IMAGE_VERSION = "${env.REFUNDS_NEW_TAG}"
    CASEWORKER_API_IMAGE_VERSION = "${env.REFUNDS_NEW_TAG}"

  }

  stages {

    stage('Init')  {

      parallel {
        stage('Notify Slack') {
          steps {
            script {
              slackContent = getBaseSlackContent('STARTED')
              echo slackContent
              slackSend(message: slackContent, color: '#FFCC00', channel: '#opg-lpa-builds')
              currentBuild.description = "Tag: ${REFUNDS_NEW_TAG}"
            }
          }
        }
        stage('Clean Env') {
          steps {
            sh 'git reset --hard HEAD && git clean -fdx'
          }
        }

        stage('Linting Prep') {
          steps {
            sh "docker pull ${PHPCS_IMAGE}"
          }
        }
      }
    }

    stage('Lint Tests') {
        parallel {
          stage('Public Front Lint') {
            steps {
                echo 'PHP_CodeSniffer PSR-2'
                dir(env.PUBLIC_FRONT_WORKSPACE_DIR) {
                    sh '''
                        docker run \
                        --rm \
                        --user `id -u` \
                        --volume $(pwd):/app \
                        ${PHPCS_IMAGE} \
                            --standard=PSR2 \
                            --report=checkstyle \
                            --report-file=frontend-pcs-checkstyle.xml \
                            --runtime-set ignore_warnings_on_exit true \
                            --runtime-set ignore_errors_on_exit true \
                            src/
                    '''
                    checkstyle pattern: 'frontend-pcs-checkstyle.xml'
                }
            }
          }
          stage('Caseworker Front Lint') {
                steps {
                    echo 'PHP_CodeSniffer PSR-2'
                      dir(env.CASEWORKER_FRONT_WORKSPACE_DIR) {
                        sh '''
                            docker run -i \
                            --rm \
                            --user `id -u` \
                            --volume $(pwd):/app \
                            ${PHPCS_IMAGE} \
                                --standard=PSR2 \
                                --report=checkstyle \
                                --report-file=backend-checkstyle.xml \
                                --runtime-set ignore_warnings_on_exit true \
                                --runtime-set ignore_errors_on_exit true \
                                src/
                        '''
                      }
                      checkstyle pattern: 'backend-checkstyle.xml'
                  }
          }
          stage('Caseworker API Lint') {
              steps {
                  echo 'PHP_CodeSniffer PSR-2'
                  dir(env.CASEWORKER_API_WORKSPACE_DIR) {
                    sh '''
                        docker run \
                        --rm \
                        --user `id -u` \
                        --volume $(pwd):/app \
                        ${PHPCS_IMAGE} \
                            --standard=PSR2 \
                            --report=checkstyle \
                            --report-file=membrane-checkstyle.xml \
                            --runtime-set ignore_warnings_on_exit true \
                            --runtime-set ignore_errors_on_exit true \
                            src/
                    '''
                   }
                   checkstyle pattern: 'membrane-checkstyle.xml'
            }
          }
        }
    }

    stage('Docker Build') {
      parallel {
          stage('Build Public Front') {
            steps {
              dir(env.PUBLIC_FRONT_WORKSPACE_DIR) {
                ansiColor('xterm') {
                  sh "docker build --pull -t ${PUBLIC_FRONT_IMAGE_FULL} ."
                }
              }
            }
          }
          stage('Build Caseworker Front') {
            steps {
              dir(env.CASEWORKER_FRONT_WORKSPACE_DIR) {
                ansiColor('xterm') {
                  sh "docker build --pull -t ${CASEWORKER_FRONT_IMAGE_FULL} ."
                }
              }
            }
          }
          stage('Build Caseworker API') {
            steps {
              dir(env.CASEWORKER_API_WORKSPACE_DIR) {
                ansiColor('xterm') {
                  sh "docker build --pull -t ${CASEWORKER_API_IMAGE_FULL} ."
                }
              }
            }
          }

      }
    }

    stage('Unit Tests') {
      parallel {
        stage('Public Front Unit Tests') {
            steps {
              ansiColor('xterm') {
                sh '''
                    mkdir -p build/output/phpunit
                    docker run \
                        --rm \
                        --volume=$(pwd)/build/output/phpunit:/app/build/output/phpunit \
                        --workdir=/app \
                        --env OPG_PHP_XDEBUG_ENABLE=1 \
                        ${PUBLIC_FRONT_IMAGE_FULL} \
                        /sbin/my_init --quiet -- \
                            sh -c 'umask 000 && \
                                php /app/vendor/bin/phpunit \
                                    --verbose \
                                    --configuration phpunit.xml.dist \
                                    --coverage-clover build/output/phpunit/coverage/public-front-phpunit/clover.xml \
                                    --coverage-html build/output/phpunit/coverage/public-front-phpunit \
                                    --exclude-group functional \
                                    --log-junit build/output/phpunit/junit/public-front-phpunit-output.xml \
                                    --testsuite 'App\\Tests' && \
                                umask 022'
                    sed -i "s#<file name=\\"/app#<file name=\\"#" build/output/phpunit/coverage/public-front-phpunit/clover.xml
                '''
              }

                // step([
                //     $class: 'CloverPublisher',
                //     cloverReportDir: 'build/output/phpunit/coverage/public-front-phpunit',
                //     cloverReportFileName: 'clover.xml'
                // ])
                //
                // fileOperations([fileZipOperation('build/output/phpunit/coverage/public-front-phpunit')])
                // archiveArtifacts artifacts: 'public-front-phpunit.zip'
            }
            post {
                always {
                    junit 'build/output/phpunit/junit/public-front-phpunit-output.xml'
                }
            }
        }
        stage('Caseworker Front Unit Tests') {
            steps {
                ansiColor('xterm') {
                  sh '''
                      mkdir -p build/output/phpunit
                      docker run \
                          --rm \
                          --volume=$(pwd)/build/output/phpunit:/app/build/output/phpunit \
                          --workdir=/app \
                          --env OPG_PHP_XDEBUG_ENABLE=1 \
                          ${CASEWORKER_FRONT_IMAGE_FULL} \
                          /sbin/my_init --quiet -- \
                              sh -c 'umask 000 && \
                                  php /app/vendor/bin/phpunit \
                                      --verbose \
                                      --configuration phpunit.xml.dist \
                                      --coverage-clover build/output/phpunit/coverage/caseworker-front-phpunit/clover.xml \
                                      --coverage-html build/output/phpunit/coverage/caseworker-front-phpunit \
                                      --exclude-group functional \
                                      --log-junit build/output/phpunit/junit/caseworker-front-phpunit-output.xml \
                                      --testsuite 'App\\Tests' && \
                              umask 022'
                      sed -i "s#<file name=\\"/app#<file name=\\"#" build/output/phpunit/coverage/caseworker-front-phpunit/clover.xml
                  '''
                }
                // step([
                //     $class: 'CloverPublisher',
                //     cloverReportDir: 'build/output/phpunit/coverage/caseworker-front-phpunit',
                //     cloverReportFileName: 'clover.xml'
                // ])
                //
                // fileOperations([fileZipOperation('build/output/phpunit/coverage/caseworker-front-phpunit')])
                // archiveArtifacts artifacts: 'caseworker-front-phpunit.zip'
            }
            post {
                always {
                    junit 'build/output/phpunit/junit/caseworker-front-phpunit-output.xml'
                }
            }
        }
        stage('Caseworker API Unit Tests') {
            steps {
                  ansiColor('xterm') {
                    sh '''
                        mkdir -p build/output/phpunit
                        docker run \
                            --rm \
                            --volume=$(pwd)/build/output/phpunit:/app/build/output/phpunit \
                            --workdir=/app \
                            --env OPG_PHP_XDEBUG_ENABLE=1 \
                            ${CASEWORKER_API_IMAGE_FULL} \
                            /sbin/my_init --quiet -- \
                                sh -c 'umask 000 && \
                                    php /app/vendor/bin/phpunit \
                                        --verbose \
                                        --configuration phpunit.xml.dist \
                                        --coverage-clover build/output/phpunit/coverage/membrane-phpunit/clover.xml \
                                        --coverage-html build/output/phpunit/coverage/membrane-phpunit \
                                        --exclude-group functional \
                                        --log-junit build/output/phpunit/junit/membrane-phpunit-output.xml \
                                        --testsuite 'App\\Tests'  && \
                                    umask 022'
                        sed -i "s#<file name=\\"/app#<file name=\\"#" build/output/phpunit/coverage/membrane-phpunit/clover.xml
                    '''
                }
                // step([
                //     $class: 'CloverPublisher',
                //     cloverReportDir: 'build/output/phpunit/coverage/membrane-phpunit',
                //     cloverReportFileName: 'clover.xml'
                // ])

                // fileOperations([fileZipOperation('build/output/phpunit/coverage/membrane-phpunit')])
                // archiveArtifacts artifacts: 'membrane-phpunit.zip'
            }
            // post {
            //     always {
            //         junit 'build/output/phpunit/junit/membrane-phpunit-output.xml'
            //     }
            // }
        }
      }
    }
  }

  // stage ('Deployment') {
    // if (isPublishingBranch()) {
        // @todo - have a stage for deploying from J1 now
    // }
  // }

  post {
      // Always cleanup docker containers, especially for aborted jobs.
      // always {
      // @todo - determine what we should do on abort, fail, success, always steps.
      always {
          echo "BUILD RESULT: ${currentBuild.currentResult}"

          script {

            // Slave: ${getSlaveHostname()}
            slackContent = getBaseSlackContent(currentBuild.currentResult)

            // If it's an aborted build, we don't want to push anything.
            if(currentBuild.currentResult != 'ABORTED') {

              slackContent = """${slackContent}
  Deploy Tag: ${REFUNDS_NEW_TAG}"""

              withCredentials([[$class: 'UsernamePasswordMultiBinding', credentialsId: githubUserPassCredentialsId, usernameVariable: 'GIT_USERNAME', passwordVariable: 'GIT_PASSWORD']]) {
                  sh '''

                  if ${CI_WORKSPACE_DIR}/docker-image-exists.sh ${PUBLIC_FRONT_IMAGE_FULL} && ${CI_WORKSPACE_DIR}/docker-image-exists.sh ${CASEWORKER_FRONT_IMAGE_FULL} && ${CI_WORKSPACE_DIR}/docker-image-exists.sh ${CASEWORKER_API_IMAGE_FULL}; then

                      echo "FOUND DOCKER IMAGES - TAGGING"
                      # git config tweak is due to a limitation on the jenkins branch sources (github) plugin
                      git config url."https://${GIT_USERNAME}:${GIT_PASSWORD}@github.com/".insteadOf "https://github.com/"
                      git tag ${REFUNDS_NEW_TAG}
                      git push origin ${REFUNDS_NEW_TAG}

                      docker push ${PUBLIC_FRONT_IMAGE_FULL}
                      docker push ${CASEWORKER_FRONT_IMAGE_FULL}
                      docker push ${CASEWORKER_API_IMAGE_FULL}
                  else
                      echo "DOCKER IMAGES NOT FOUND - NO IMAGES TO PUSH"
                  fi
                  '''
              }
            }

            // Only when it's a successfull pipeline run, we clean it up. This means bad builds can be debugged on the CI box
            echo "SUCESSFUL PIPELINE - REMOVING IMAGES"
            dir(env.CI_WORKSPACE_DIR) {
                // Clean up docker images
                sh '''
                docker rmi -f ${PUBLIC_FRONT_IMAGE_FULL}
                docker rmi -f ${CASEWORKER_FRONT_IMAGE_FULL}
                docker rmi -f ${CASEWORKER_API_IMAGE_FULL}
                make j2_cleanup_pipeline_run || echo "Cleanup failed - this is acceptable as cleanups are a nice to have"
                '''
                // Clean up docker networks
                sh 'docker network prune -f'
            }

            // SLACK STUFF
            slackChannel = '#opg-lpa-builds'

            colorCode = 'danger'
            if(currentBuild.currentResult == 'SUCCESS') {
              colorCode = 'good'
            } else if(currentBuild.currentResult == 'ABORTED') {
              colorCode = '#808080'
            }

            echo slackContent
            slackSend(message: slackContent, color: colorCode, channel: slackChannel)

          }

          // @todo - slack

      } // end of always{}
  } // end of post{}
} // end of pipeline{}
