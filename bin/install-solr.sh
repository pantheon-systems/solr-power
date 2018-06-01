#!/usr/bin/env bash

SOLR_PORT=${SOLR_PORT:-8983}

download() {
    echo "Downloading solr from $1..."
    curl -s $1 | tar xz
    echo "Downloaded"
}

is_solr_up(){
    http_code=`echo $(curl -s -o /dev/null -w "%{http_code}" "http://localhost:$SOLR_PORT/solr/admin/ping")`
    return `test $http_code = "200"`
}

wait_for_solr(){
    while ! is_solr_up; do
        sleep 3
    done
}

run() {
    echo "Starting solr on port ${SOLR_PORT}..."

    cd $1/example
    if [ $DEBUG ]
    then
        java -Djetty.port=$SOLR_PORT -jar start.jar &
    else
        java -Djetty.port=$SOLR_PORT -jar start.jar > /dev/null 2>&1 &
    fi
    wait_for_solr
    cd ../../
    echo "Started"
}

post_some_documents() {
    java -Dtype=application/json -Durl=http://localhost:$SOLR_PORT/solr/update/json -jar $1/example/exampledocs/post.jar $2
}


download_and_run() {

   
    # Download from a Pantheon hosted file on CI
    if $CONTINUOUS_INTEGRATION
    then
    	url="https://dev-solr-power.pantheonsite.io/wp-content/uploads/apache-solr-3.6.2.tgz"
    else
    	url="http://archive.apache.org/dist/lucene/solr/3.6.2/apache-solr-3.6.2.tgz"
    fi
    dir_name="apache-solr-3.6.2"
    dir_conf="conf/"

    download $url

    # copy schema.xml
    cp schema.xml $dir_name/example/solr/$dir_conf

    # Run solr
    run $dir_name $SOLR_PORT


}

download_and_run $SOLR_VERSION
