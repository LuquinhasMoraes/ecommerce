<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v8/errors/resource_access_denied_error.proto

namespace GPBMetadata\Google\Ads\GoogleAds\V8\Errors;

class ResourceAccessDeniedError
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();
        if (static::$is_initialized == true) {
          return;
        }
        \GPBMetadata\Google\Api\Http::initOnce();
        \GPBMetadata\Google\Api\Annotations::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
Agoogle/ads/googleads/v8/errors/resource_access_denied_error.protogoogle.ads.googleads.v8.errors"s
ResourceAccessDeniedErrorEnum"R
ResourceAccessDeniedError
UNSPECIFIED 
UNKNOWN
WRITE_ACCESS_DENIEDB�
"com.google.ads.googleads.v8.errorsBResourceAccessDeniedErrorProtoPZDgoogle.golang.org/genproto/googleapis/ads/googleads/v8/errors;errors�GAA�Google.Ads.GoogleAds.V8.Errors�Google\\Ads\\GoogleAds\\V8\\Errors�"Google::Ads::GoogleAds::V8::Errorsbproto3'
        , true);
        static::$is_initialized = true;
    }
}

