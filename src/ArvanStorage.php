<?php

namespace Omidrezasalari\ArvanStorage;

use Aws\S3\S3Client;

class ArvanStorage
{
    private $awsKey, $awsSecretKey, $endPoint, $client;

    public function __construct()
    {
        $this->awsKey = config('arvan_config.aws_key');
        $this->awsSecretKey = config('arvan_config.aws_secret_key');
        $this->endPoint = config('arvan_config.end_point');
        $this->clientIsExist();
    }

    private function awsKey()
    {
        return $this->awsKey;
    }

    private function awsSecretkey()
    {
        return $this->awsSecretKey;
    }

    private function endpoint()
    {
        return $this->endPoint;
    }

    /**
     * Set Client
     * @param S3Client $client
     *
     * @return void
     */
    private function setClient(S3Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get Client
     *
     * @return S3Client
     */
    private function getClient()
    {
        return $this->client;
    }

    /**
     * make Aws connection
     */
    private function connection()
    {

        $client = new S3Client([

            'region' => '',

            'version' => '2006-03-01',

            'endpoint' => env("AWS_ENDPOINT"),

            'credentials' => [

                'key' => env('AWS_ACCESS_KEY_ID'),

                'secret' => env('AWS_SECRET_ACCESS_KEY')

            ], 'use_path_style_endpoint' => true

        ]);

        $this->setClient($client);
    }


    /**
     * Check client is exist.
     */
    private function clientIsExist()
    {
        return $this->getClient() ?? $this->connection();
    }

    /**
     * View your own buckets.
     */
    public function listOwnBuckets()
    {
        $listResponse = $this->getClient()->listBuckets();
        $buckets = $listResponse['Buckets'];

        return $buckets;
    }

    /**
     * create new bucket.
     *
     * @param string $bucketName
     */
    public function createBucket(string $bucketName, $option = "public-read")
    {
        $result = $this->getClient()->createBucket([
            'ACL' => $option,
            'Bucket' => $bucketName,
        ]);

        return 'The bucket\'s location is: ' .
            $result['Location'] . '. ' .
            'The bucket\'s effective URI is: ' .
            $result['@metadata']['effectiveUri'];
    }

    /**
     * Bucket content lists
     *
     * @param $bucketName
     */
    public function bucketContentList($bucketName)
    {
        $objectsListResponse = $this->getClient()->listObjects(['Bucket' => $bucketName]);
        $objects = $objectsListResponse['Contents'] ?? [];
        return $objects;
    }

    /**
     * delete bucket
     *
     * @param $bucketName
     */
    public function deleteBucket($bucketName)
    {
        return $this->getClient()->deleteBucket(['Bucket' => $bucketName]);
    }

    /**
     * Get bucket acl
     *
     * @param $bucketName
     *
     * @return \Aws\Result
     */
    public function getBucketAcl($bucketName)
    {
        return $resp = $this->getClient()->getBucketAcl([
            'Bucket' => $bucketName
        ]);
    }

    /**
     * put bucket acl
     *
     * @param string $bucketName
     * @param string $acl
     */
    public function putBucketAcl($bucketName, $acl = 'public-read')
    {
        $resp = $this->getClient()->putBucketAcl([
            'ACL' => 'public-read',
            'Bucket' => $bucketName,
        ]);
    }

    /**
     * create new object
     *
     * @param $bucketName
     * @param $fileName
     * @param $content
     */
    public function createObject($bucketName, $fileName, $content)
    {
        $this->getClient()->putObject([
            'Bucket' => $bucketName,
            'Key' => $fileName,
            'Body' => $content
        ]);
    }

    public function putObject($prefix = null, $file, $backetName, $acl = "public-read")
    {
        try {
            $prefix = $prefix ?? time();
            $fileName = $prefix . "-" . $file->getClientOriginalName();
            //            $objects = $this->bucketContentList($backetName);
            //
            //            if (in_array($fileName, $objects)) {
            //                return $fileName;
            //            }
            $this->getClient()->putObject([
                'Bucket' => $backetName,
                'Key' => $fileName,
                'SourceFile' => $file,
                'ACL' => $acl
            ]);
            return $fileName;
        } catch (S3Exception $e) {
            return $e->getMessage() . "\n";
        }
    }

    public function uploadObject($fileName, $file, $backetName, $acl = "public-read")
    {
        try {
            $this->getClient()->putObject([
                'Bucket' => $backetName,
                'Key' => $fileName,
                'SourceFile' => $file,
                'ACL' => $acl
            ]);

            return $fileName;
        } catch (S3Exception $e) {
            return $e->getMessage() . "\n";
        }
    }

    public function getObjectUrl($bucket, $fileName, $ttl)
    {
        // Get a pre-signed URL for an Amazon S3 object
        $signedUrl = $this->getClient()
            ->getObjectUrl($bucket, $fileName, $ttl);
    }

    /**
     * change object acl.
     *
     * @param $bucketName
     * @param $fileName
     * @param $acl
     */
    public function changeObjectAcl($bucketName, $fileName, $acl)
    {
        $this->getClient()->putObjectAcl([
            'Bucket' => $bucketName,
            'Key' => $fileName,
            'ACL' => $acl
        ]);
    }

    /**
     * delete a object
     *
     * @param $bucketName
     * @param $fileName
     */
    public function deleteObject($bucketName, $fileName)
    {
        $this->getClient()
            ->deleteObject(['Bucket' => $bucketName, 'Key' => $fileName]);
    }

    /**
     * download a object
     *
     * @param $bucketName
     * @param $fileName
     */
    public function downloadObject($bucketName, $fileName)
    {
        $object = $this->getClient()
            ->getObject(['Bucket' => $bucketName, 'Key' => $fileName]);
        file_put_contents('/home/larry/documents/poetry.pdf', $object['Body']->getContents());
    }

    /**
     * create download url
     *
     * @param $bucketName
     * @param $fileName
     */
    public function createDownloadUrl($bucketName, $fileName)
    {
        $hello_url = $this->getClient()
            ->getObjectUrl($bucketName, $fileName);
        //        echo $hello_url . "\n";

        $secret_plans_cmd = $this->getClient()
            ->getCommand('GetObject', ['Bucket' => $bucketName, 'Key' => $fileName]);
        $request = $this->getClient()
            ->createPresignedRequest($secret_plans_cmd, '+1 hour');
        //        echo $request->getUri() . "\n";
    }
}
