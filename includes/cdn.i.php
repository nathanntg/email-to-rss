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
            self::$_s3 = \Aws\S3\S3Client::factory([
                'key' => configurationGet('AWS_KEY'),
                'secret' => configurationGet('AWS_SECRET'),
                'region' => 'us-east-1'
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
        catch (\Aws\S3\Exception\NoSuchKeyException $e) {
            return false;
        }
        catch (Exception $e) {
            Log::addError('Unable to get existing file from CDN: ' . $e->getMessage());

            throw $e;
        }

		// get the contents
		$content = (string)$result['Body'];

        return $content;
    }

    public static function putFile($name, $body) {
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
				'ACL'           => \Aws\S3\Enum\CannedAcl::PUBLIC_READ,
                'StorageClass'  => \Aws\S3\Enum\StorageClass::REDUCED_REDUNDANCY
            ]);
        }
        catch (Exception $e) {
            Log::addError('Unable to put file on CDN: ' . $e->getMessage());

            throw $e;
        }

        return true;
    }
}
