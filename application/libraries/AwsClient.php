<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;

class AwsClient {
    protected $s3;

    public function __construct()
    {
        $credentials = new Credentials(AMAZON_ACCESS_KEY, AMAZON_SECRET_KEY);

        try {
            $this->s3 = new S3Client([
                'credentials'=> $credentials,
                'region' => "us-east-1",
                'version' => "latest",
                'http' => [ 'verify' => false ],
                'suppress_php_deprecation_warning' => true
            ]);
        } catch (AwsException $th) {
            log_message('error', "AWS sdk error: " . $th->getMessage());
            throw new Exception("Could not connect");
        }
    }

    public function upload_file_bucket($destinationPath, $sourcePath)
    {
        try {
            $result = $this->s3->putObject([
                'Bucket' => BUCKET_NAME,
                'Key' => $destinationPath,
                'SourceFile' => $sourcePath,
                // 'ACL' => 'public-read'
            ]);

            return $result['ObjectURL'];
        } catch (Aws\S3\Exception\S3Exception $e) {
            log_message('error', "AWS sdk error: " . $e->getMessage());
            return false;
        }
    }

    public function remove_file_bucket($sourcePath)
    {
        $newString = preg_replace('/https:\/\/'.BUCKET_NAME.'\.s3\.amazonaws\.com\//', '', $sourcePath);
        try {
            $result = $this->s3->deleteObject([
                'Bucket' => BUCKET_NAME,
                'Key' => $newString,
            ]);

            return true;
        } catch (Aws\S3\Exception\S3Exception $e) {
            log_message('error', "AWS sdk error: " . $e->getMessage());
            // throw new Exception($e->getMessage());
            return false;
        }
    }
}
?>