<?php

class Cdn
{
    private static $_s3;

    /**
     * @return \Aws\S3\S3Client
     */
    private static function _getS3() {
        if (!isset(self::$_s3)) {
            // configure S3
            self::$_s3 = new \Aws\S3\S3Client([
	            'version' => '2006-03-01',
	            'region' => 'us-east-2',
	            'credentials' => [
		            'key' => configurationGet('AWS_KEY'),
                    'secret' => configurationGet('AWS_SECRET'),
	            ],
            ]);
        }

        return self::$_s3;
    }

    public static function getFile($name) {
        $s3 = self::_getS3();

        // get bucket and prefix
        $bucket = AWS_BUCKET;
        $prefix = AWS_PREFIX;

        // assemble key
        $key = sprintf('%s%s' , $prefix, $name);

        try {
            $result = $s3->getObject([
                'Bucket'    => $bucket,
                'Key'       => $key
            ]);
        }
        catch (\Aws\S3\Exception\S3Exception $e) {
	        switch ($e->getAwsErrorCode()) {
		        case 'NoSuchKey':
		        case 'NotFound':
			        return false;

		        default:
			        Log::addError('Unable to get existing file from CDN: ' . $e->getMessage());
			        throw $e;
	        }
        }
        catch (Exception $e) {
            Log::addError('Unable to get existing file from CDN: ' . $e->getMessage());

            throw $e;
        }

		// get the contents
		$content = (string)$result['Body'];

        return $content;
    }

    public static function putFile($name, $body, $content_type=null) {
        $s3 = self::_getS3();

        // get bucket and prefix
        $bucket = AWS_BUCKET;
        $prefix = AWS_PREFIX;

        // assemble key
        $key = sprintf('%s%s' , $prefix, $name);

        // put the object
        try {
            $result = $s3->putObject([
                'Bucket'        => $bucket,
                'Key'           => $key,
				'Body'          => $body,
				'ACL'           => 'public-read',
                'ContentType'   => $content_type
            ]);
        }
        catch (Exception $e) {
            Log::addError('Unable to put file on CDN: ' . $e->getMessage());

            throw $e;
        }

        return true;
    }
}
