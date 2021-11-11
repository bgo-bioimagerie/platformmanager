#!/bin/bash

# Build docker based on version
# if no tag provided, check for Github CI and use GITHUB_REF env var else use latest tag on branch
#
# Examples:
#   bin/docker-build.sh --tag 2.1.8 --branch maintenance_21  # use latest of branch and tag 2.1.8
#   bin/docker-build.sh  # use CI branch and tag or if not defined current branch and latest tag
#   bin/docker-build.sh --tag 2.1.8  # build against tag 2.18

TAGS=()
CURRENT=''
BRANCH='master'


CMDTAG=''
CMDBRANCH=''
DRY='NO'
while [[ $# -gt 0 ]]; do
  key="$1"

  case $key in
    -t|--tag)
      CMDTAG="$2"
      shift # past argument
      shift # past value
      ;;
    -b|--branch)
      CMDBRANCH="$2"
      shift # past argument
      shift # past value
      ;;
    --dry)
      DRY='YES'
      shift # past argument
      ;;
    *)    # unknown option
      shift # past argument
      ;;
  esac
done

echo "Command args: tag=${CMDTAG}, branch=${CMDBRANCH}, dry=${DRY}"
if [ -z $CMDTAG ]; then
  echo "No tag specified, guess it"
  if [ -z "$GITHUB_REF"]; then
    CURRENT=`git describe --tags --abbrev=0`
    TAGS+=("${CURRENT}")
  else
    GHTAG=$GITHUB_REF
    if [[ "${GHTAG}" =~ refs/tags/(.*) ]]; then
      CURRENT="${BASH_REMATCH[1]}"
      TAGS+=("${CURRENT}")
    fi
  fi
  BRANCH=${CURRENT}
else
  CURRENT=${CMDTAG}
  if [ "$CURRENT" != "latest" ]; then
    TAGS+=("${CURRENT}")
    BRANCH=$CURRENT
  else
    echo "build latest from current branch"
    BRANCH=`git rev-parse --abbrev-ref HEAD`
  fi
fi

if [ "$CMDBRANCH" != "" ]; then
  BRANCH=$CMDBRANCH
fi

echo "Current: ${BRANCH}/${CURRENT}"

if [[ "${CURRENT}" =~ ^([0-9]+\.[0-9]+)\.[0-9]+$ ]]; then
  # if a.b.c tag also as a.b
  echo "Minor tag, tag also as major tag"
  TAGS+=("${BASH_REMATCH[1]}")
elif [[ "${CURRENT}" =~ ^([0-9]+\.[0-9]+)$ ]]; then
  # if a.b tag also as latest
  echo "Major tag, tag also as latest tag"
  TAGS+=("latest")
else
  echo "No tag, use latest"
  TAGS+=("latest")
fi


( IFS=$','; echo "Tags: ${TAGS[*]}" )

IMAGE="${PFM_IMAGE:-pfm}"
REPO="${PFM_REPO:-quay.io}"
NAMESPACE="${PFM_NS:-genouest}"

echo "Build image ${REPO}/${NAMESPACE}/${IMAGE}/${CURRENT} from ${BRANCH}"
if [ "$DRY" == "YES" ]; then
  echo "  docker build -f docker/Dockerfile --build-arg BRANCH=${BRANCH} -t ${REPO}/${NAMESPACE}/${IMAGE}:${CURRENT} ./docker"
else
  docker build -f docker/Dockerfile --build-arg BRANCH=${BRANCH} -t ${REPO}/${NAMESPACE}/${IMAGE}:${CURRENT} ./docker
fi

for i in "${TAGS[@]}"
do
  if [ "$CURRENT" != "$i" ]; then
    echo "Tag image ${REPO}/${NAMESPACE}/${IMAGE}:${i}"
    if [ "$DRY" == "YES" ]; then
      echo "  docker tag ${REPO}/${NAMESPACE}/${IMAGE}:${CURRENT} ${REPO}/${NAMESPACE}/${IMAGE}:${i}"
    else
      docker tag ${REPO}/${NAMESPACE}/${IMAGE}:${CURRENT} ${REPO}/${NAMESPACE}/${IMAGE}:${i}
    fi
  fi
done

for i in "${TAGS[@]}"
do
  echo "Push image ${REPO}/${NAMESPACE}/${IMAGE}:${i}"
  echo "  docker push ${REPO}/${NAMESPACE}/${IMAGE}:${i}"
done