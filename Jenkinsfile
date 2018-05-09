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
def repoName = 'opg-sirius'
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

    def githubRepo = 'https://github.com/ministryofjustice/opg-sirius/'
    def githubBranchUrl = githubRepo + 'tree/' + getSiriusBranchName()
    return githubBranchUrl;
}

// Because we can build both branches and pull requests, we don't want the release tag to container PR-95
// as it's not descriptive, instead we want the originating branch name i.e: SW-50. env.CHANGE_BRANCH contains this value if it's a PR being built.
def getSiriusBranchName() {
    if(env.CHANGE_BRANCH != null) {
        return env.CHANGE_BRANCH
    }
    return env.BRANCH_NAME
}

def getBaseSlackContent(currentResult) {
    // Slave: ${getSlaveHostname()}
    def blueOceanUrl = env.RUN_DISPLAY_URL
    def slackContent = """BUILD ${currentResult}
Branch: <${getGitHubBranchUrl()}|${getSiriusBranchName()}>
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
    FRONTEND_WORKSPACE_DIR = "${env.WORKSPACE}/front-end"
    BACKEND_WORKSPACE_DIR = "${env.WORKSPACE}/back-end"
    MEMBRANE_WORKSPACE_DIR = "${env.WORKSPACE}/auth-membrane"
    SMOKE_TEST_DIR = "${env.WORKSPACE}/front-end/supervision-ui-tests"

    BACKEND_IMAGE = 'opguk/core-api'
    FRONTEND_IMAGE = 'opguk/core-frontend'
    MEMBRANE_IMAGE = 'opguk/core-membrane'
    SMOKE_TEST_IMAGE = 'opguk/core-smoke-test'
    PHPCS_IMAGE = "${DOCKER_REGISTRY}/opguk/phpcs"
    NODE_RUNNER_IMAGE = 'opguk/node-runner'
    NODE_RUNNER_TAG = "latest"

    SIRIUS_NEW_TAG = "${getSiriusBranchName()}__${dateString}__${env.BUILD_NUMBER}"

    BACKEND_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.BACKEND_IMAGE}:${env.SIRIUS_NEW_TAG}"
    FRONTEND_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.FRONTEND_IMAGE}:${env.SIRIUS_NEW_TAG}"
    MEMBRANE_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.MEMBRANE_IMAGE}:${env.SIRIUS_NEW_TAG}"
    NODE_RUNNER_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.NODE_RUNNER_IMAGE}:${env.NODE_RUNNER_TAG}"
    SMOKE_TEST_IMAGE_FULL = "${env.DOCKER_REGISTRY}/${env.SMOKE_TEST_IMAGE}:${env.SIRIUS_NEW_TAG}"

    FRONTEND_IMAGE_VERSION = "${env.SIRIUS_NEW_TAG}"
    BACKEND_IMAGE_VERSION = "${env.SIRIUS_NEW_TAG}"
    MEMBRANE_IMAGE_VERSION = "${env.SIRIUS_NEW_TAG}"

  }

  stages {

    stage('Init')  {

      parallel {
        stage('Notify Slack') {
          steps {
            script {
              slackContent = getBaseSlackContent('STARTED')
              echo slackContent
              slackSend(message: slackContent, color: '#FFCC00', channel: '#opg-sirius-builds')
              currentBuild.description = "Tag: ${SIRIUS_NEW_TAG}"
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
          stage('Backend Lint') {
                steps {
                    echo 'PHP_CodeSniffer PSR-2'
                      dir(env.BACKEND_WORKSPACE_DIR) {
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
                                module/Application/src/
                        '''
                      }
                      checkstyle pattern: 'backend-checkstyle.xml'
                  }
          }
          stage('Frontend Lint') {
            steps {
                echo 'PHP_CodeSniffer PSR-2'
                dir(env.FRONTEND_WORKSPACE_DIR) {
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
                            module/Application/src/
                    '''
                    checkstyle pattern: 'frontend-pcs-checkstyle.xml'
                }

            }
          }
          stage('Membrane Lint') {
              steps {
                  echo 'PHP_CodeSniffer PSR-2'
                  dir(env.MEMBRANE_WORKSPACE_DIR) {
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
                            module/Application/src/
                    '''
                   }
                   checkstyle pattern: 'membrane-checkstyle.xml'
            }
          }
          stage('Compile Assets Prep') {
            steps {
              dir(env.FRONTEND_WORKSPACE_DIR) {
                sh "docker build -t ${NODE_RUNNER_IMAGE_FULL} -f Dockerfile.node_runner ."
              }
            }
          }
        }
    }

    stage('Compile Assets (Node)') {
      parallel {
        stage('LPA Node Deps') {
          steps {
            dir(env.FRONTEND_WORKSPACE_DIR) {
              ansiColor('xterm') {
                sh "./docker/node/node_runner.sh lpa"
              }
            }
          }
        }

        stage('LPA Tests Node Deps') {
          steps {
            dir(env.FRONTEND_WORKSPACE_DIR) {
              ansiColor('xterm') {
                sh "./docker/node/node_runner.sh lpa_tests"
              }
            }
          }
        }

        stage('Supervision Node Deps') {
          steps {
            dir(env.FRONTEND_WORKSPACE_DIR) {
              ansiColor('xterm') {
                sh "./docker/node/node_runner.sh supervision_setup_prod"
              }
            }
          }
        }

        stage('Protractor Node Test Deps') {
          steps {
            dir(env.FRONTEND_WORKSPACE_DIR) {
              ansiColor('xterm') {
                sh "./docker/node/node_runner.sh supervision_tests"
              }
            }
          }
        }

        stage('Supervision UI Test Deps') {
          steps {
            dir(env.FRONTEND_WORKSPACE_DIR) {
              ansiColor('xterm') {
                sh "./docker/node/node_runner.sh supervision_e2e_tests_setup"
              }
            }
          }
        }
      }
    }

    stage('Docker Build') {
      parallel {
          stage('Build Backend') {
            steps {
              dir(env.BACKEND_WORKSPACE_DIR) {
                ansiColor('xterm') {
                  sh "docker build --pull -t ${BACKEND_IMAGE_FULL} ."
                }
              }
            }
          }
          stage('Build Frontend') {
            steps {
              dir(env.FRONTEND_WORKSPACE_DIR) {
                ansiColor('xterm') {
                  sh "docker build --pull -t ${FRONTEND_IMAGE_FULL} ."
                }
              }
            }
          }
          stage('Build Membrane') {
            steps {
              dir(env.MEMBRANE_WORKSPACE_DIR) {
                ansiColor('xterm') {
                  sh "docker build --pull -t ${MEMBRANE_IMAGE_FULL} ."
                }
              }
            }
          }
          stage('Build Smoke Test') {
            steps {
              dir(env.FRONTEND_WORKSPACE_DIR) {
                ansiColor('xterm') {
                  sh "docker build --pull -t ${SMOKE_TEST_IMAGE_FULL} -f Dockerfile.smoke_tests ./supervision-ui-tests" 
                }
              }
            }
          }

      }
    }

    stage('Unit Tests') {
      parallel {
        stage('Backend Unit Tests') {
            steps {
                ansiColor('xterm') {
                  sh '''
                      mkdir -p build/output/phpunit
                      docker run \
                          --rm \
                          --volume=$(pwd)/build/output/phpunit:/app/build/output/phpunit \
                          --workdir=/app \
                          --env OPG_PHP_XDEBUG_ENABLE=1 \
                          ${BACKEND_IMAGE_FULL} \
                          /sbin/my_init --quiet -- \
                              sh -c 'umask 000 && \
                                  php /app/vendor/bin/phpunit \
                                      --verbose \
                                      --configuration tests/phpunit.xml \
                                      --coverage-clover build/output/phpunit/coverage/back-end-phpunit/clover.xml \
                                      --coverage-html build/output/phpunit/coverage/back-end-phpunit \
                                      --exclude-group functional \
                                      --log-junit build/output/phpunit/junit/back-end-phpunit-output.xml \
                                      --testsuite unit && \
                              umask 022'
                      sed -i "s#<file name=\\"/app#<file name=\\"#" build/output/phpunit/coverage/back-end-phpunit/clover.xml
                  '''
                }
                // step([
                //     $class: 'CloverPublisher',
                //     cloverReportDir: 'build/output/phpunit/coverage/back-end-phpunit',
                //     cloverReportFileName: 'clover.xml'
                // ])
                //
                // fileOperations([fileZipOperation('build/output/phpunit/coverage/back-end-phpunit')])
                // archiveArtifacts artifacts: 'back-end-phpunit.zip'
            }
            post {
                always {
                    junit 'build/output/phpunit/junit/back-end-phpunit-output.xml'
                }
            }
        }
        stage('Membrane Unit Tests') {
            steps {
                  ansiColor('xterm') {
                    sh '''
                        mkdir -p build/output/phpunit
                        docker run \
                            --rm \
                            --volume=$(pwd)/build/output/phpunit:/app/build/output/phpunit \
                            --workdir=/app \
                            --env OPG_PHP_XDEBUG_ENABLE=1 \
                            ${MEMBRANE_IMAGE_FULL} \
                            /sbin/my_init --quiet -- \
                                sh -c 'umask 000 && \
                                    php /app/vendor/bin/phpunit \
                                        --verbose \
                                        --configuration tests/phpunit.xml \
                                        --coverage-clover build/output/phpunit/coverage/membrane-phpunit/clover.xml \
                                        --coverage-html build/output/phpunit/coverage/membrane-phpunit \
                                        --exclude-group functional \
                                        --log-junit build/output/phpunit/junit/membrane-phpunit-output.xml \
                                        --testsuite unit  && \
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
        stage('Frontend Unit Tests') {
            steps {
              ansiColor('xterm') {
                sh '''
                    mkdir -p build/output/phpunit
                    docker run \
                        --rm \
                        --volume=$(pwd)/build/output/phpunit:/app/build/output/phpunit \
                        --workdir=/app \
                        --env OPG_PHP_XDEBUG_ENABLE=1 \
                        ${FRONTEND_IMAGE_FULL} \
                        /sbin/my_init --quiet -- \
                            sh -c 'umask 000 && \
                                php /app/vendor/bin/phpunit \
                                    --verbose \
                                    --configuration tests/phpunit.xml \
                                    --coverage-clover build/output/phpunit/coverage/front-end-phpunit/clover.xml \
                                    --coverage-html build/output/phpunit/coverage/front-end-phpunit \
                                    --exclude-group functional \
                                    --log-junit build/output/phpunit/junit/front-end-phpunit-output.xml \
                                    --testsuite unit && \
                                umask 022'
                    sed -i "s#<file name=\\"/app#<file name=\\"#" build/output/phpunit/coverage/front-end-phpunit/clover.xml
                '''
              }

                // step([
                //     $class: 'CloverPublisher',
                //     cloverReportDir: 'build/output/phpunit/coverage/front-end-phpunit',
                //     cloverReportFileName: 'clover.xml'
                // ])
                //
                // fileOperations([fileZipOperation('build/output/phpunit/coverage/front-end-phpunit')])
                // archiveArtifacts artifacts: 'front-end-phpunit.zip'
            }
            post {
                always {
                    junit 'build/output/phpunit/junit/front-end-phpunit-output.xml'
                }
            }
        }
        stage('Frontend LPA Karma unit tests') {
            steps {
              script {
                dir(env.FRONTEND_WORKSPACE_DIR) {
                  ansiColor('xterm') {
                      sh '''
                          mkdir -p build/output/lpa-karma
                          docker run \
                              --rm \
                              -v $PWD:/app \
                              -v $(pwd)/build/output/lpa-karma:/app/public/test \
                              -w /app/public \
                              ${NODE_RUNNER_IMAGE_FULL} \
                              sh -c 'umask 000 && \
                                  node ./node_modules/karma/bin/karma start karma.conf.js --colors --singleRun=true && \
                                  umask 022'
                      '''
                  }
                }
              }
            }
            post {
              always {
                dir(env.FRONTEND_WORKSPACE_DIR) {
                  junit 'build/output/lpa-karma/*.xml'
                }
              }
            }
        }
        stage('Frontend Supervision Karma unit tests') {
            steps {
              script {
                dir(env.FRONTEND_WORKSPACE_DIR) {
                  ansiColor('xterm') {
                      sh '''
                          mkdir -p build/output/supervision-karma
                          docker run \
                              --rm \
                              -v $PWD:/app \
                              -v $(pwd)/build/output/supervision-karma:/app/supervision/config/test \
                              -w /app/supervision \
                              ${NODE_RUNNER_IMAGE_FULL} \
                              sh -c 'umask 000 && \
                                  node ./node_modules/karma/bin/karma start config/karma.config.js --colors --singleRun=true && \
                                  umask 022'
                      '''
                  }
                }
              }
            }
            post {
              always {
                dir(env.FRONTEND_WORKSPACE_DIR) {
                  junit 'build/output/supervision-karma/*.xml'
                }
              }
            }
        } // /stage
        stage('Integration Prep (Ingest)') {
          steps {
            dir(env.CI_WORKSPACE_DIR) {
              script {
                ansiColor('xterm') {
                  sh '''
                  make j2_ingest
                  '''
                }
              }
            }
          }
        }
      }
    }

    //
    stage('Run integration tests') {
      parallel {
        stage('Membrane Functional') {
          steps {
            dir(env.CI_WORKSPACE_DIR) {
              ansiColor('xterm') {
                sh '''
                  mkdir -p build/output/functional-membrane
                  make j2_testsuite_phpunit_functional_membrane args="--stop-on-error --stop-on-failure --log-junit /app/build/output/functional-membrane/junit.xml"
                '''
              }
            }
          }
          post {
            always {
              dir(env.CI_WORKSPACE_DIR) {
                junit 'build/output/functional-membrane/*.xml'
              }
            }
          }
        }
        stage('Frontend Functional') {
          steps {
            dir(env.CI_WORKSPACE_DIR) {
              ansiColor('xterm') {
                sh '''
                  mkdir -p build/output/functional-frontend
                  make j2_testsuite_phpunit_functional_frontend args="--stop-on-error --stop-on-failure --log-junit /app/build/output/functional-frontend/junit.xml"
                '''
              }
            }
          }
          post {
            always {
              dir(env.CI_WORKSPACE_DIR) {
                junit 'build/output/functional-frontend/*.xml'
              }
            }
          }
        }
        stage('API Behat V0') {
          steps {
            dir(env.CI_WORKSPACE_DIR) {
              ansiColor('xterm') {
                  sh '''
                    mkdir -p build/output/behatv0
                    make j2_testsuite_behat_v0 args="--tags=@ci --stop-on-failure -f junit -o /app/build/output/behatv0 -f progress -o std"
                  '''
              }
            }
          }
          post {
            always {
              dir(env.CI_WORKSPACE_DIR) {
                junit 'build/output/behatv0/*.xml'
              }
            }
          }
        }
        stage('API Behat V1') {
          steps {
            dir(env.CI_WORKSPACE_DIR) {
              ansiColor('xterm') {
                sh '''
                    mkdir -p build/output/behatv1
                    make j2_testsuite_behat_v1 args="--tags=@ci --stop-on-failure -f junit -o /app/build/output/behatv1 -f progress -o std"
                  '''
              }
            }
          }
          post {
            always {
              dir(env.CI_WORKSPACE_DIR) {
                junit 'build/output/behatv1/*.xml'
              }
            }
          }
        }
        stage('API Functional') {
          steps {
            dir(env.CI_WORKSPACE_DIR) {
              ansiColor('xterm') {
                sh '''
                  mkdir -p build/output/functional-api
                  make j2_testsuite_phpunit_functional_api args="--stop-on-error --stop-on-failure --log-junit build/output/functional-api/junit.xml"
                  '''
              }
            }
          }
          post {
            always {
              dir(env.CI_WORKSPACE_DIR) {
                junit 'build/output/functional-api/*.xml'
              }
            }
          }
        }

        stage('Smoke Tests') {
          steps {
            dir(env.CI_WORKSPACE_DIR) {
              ansiColor('xterm') {
                // @todo - specify docker-e2e-tag @smoke here when there are Smoke tests
                sh './run_supervision_e2e_tests.sh docker-e2e || true'
              }
            }
          }
        }

        stage('Supervision Protractor') {
          steps {
            script {
              ansiColor('xterm') {
                dir(env.CI_WORKSPACE_DIR) {
                try {
                    sh './run_protractor_supervision_suite.sh'
                  } catch (exception) {
                     sh './run_serenity_report.sh'

                     publishHTML([
                       allowMissing: false,
                       alwaysLinkToLastBuild: false,
                       keepAll: false,
                       reportDir: 'protractor-serenity-report',
                       reportFiles: 'index.html',
                       reportName: 'Supervision Serenity Report',
                       reportTitles: "Tag: ${SIRIUS_NEW_TAG}"
                     ])

                     fileOperations([fileZipOperation('protractor-serenity-report')])
                     archiveArtifacts artifacts: 'protractor-serenity-report.zip'

                     throw exception
                  }
                }
              }
            }
          }
          post {
            always {
              dir(env.CI_WORKSPACE_DIR) {
                junit 'protractor-report-target/*.xml'
              }
            }
          }
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

            // If it's an aborted build, we don't wnat to push anything.
            if(currentBuild.currentResult != 'ABORTED') {

              slackContent = """${slackContent}
  Deploy Tag: ${SIRIUS_NEW_TAG}"""

              withCredentials([[$class: 'UsernamePasswordMultiBinding', credentialsId: githubUserPassCredentialsId, usernameVariable: 'GIT_USERNAME', passwordVariable: 'GIT_PASSWORD']]) {
                  sh '''

                  if ${CI_WORKSPACE_DIR}/docker-image-exists.sh ${BACKEND_IMAGE_FULL} && ${CI_WORKSPACE_DIR}/docker-image-exists.sh ${FRONTEND_IMAGE_FULL} && ${CI_WORKSPACE_DIR}/docker-image-exists.sh ${MEMBRANE_IMAGE_FULL}; then

                      echo "FOUND DOCKER IMAGES - TAGGING"
                      # git config tweak is due to a limitation on the jenkins branch sources (github) plugin
                      git config url."https://${GIT_USERNAME}:${GIT_PASSWORD}@github.com/".insteadOf "https://github.com/"
                      git tag ${SIRIUS_NEW_TAG}
                      git push origin ${SIRIUS_NEW_TAG}

                      docker push ${BACKEND_IMAGE_FULL}
                      docker push ${FRONTEND_IMAGE_FULL}
                      docker push ${MEMBRANE_IMAGE_FULL}
                      docker push ${SMOKE_TEST_IMAGE_FULL}
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
                docker rmi -f ${BACKEND_IMAGE_FULL}
                docker rmi -f ${FRONTEND_IMAGE_FULL}
                docker rmi -f ${MEMBRANE_IMAGE_FULL}
                docker rmi -f ${SMOKE_TEST_IMAGE_FULL}
                make j2_cleanup_pipeline_run || echo "Cleanup failed - this is acceptable as cleanups are a nice to have"
                '''
                // Clean up docker networks
                sh 'docker network prune -f'
            }

            // SLACK STUFF
            slackChannel = '#opg-sirius-builds'

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
