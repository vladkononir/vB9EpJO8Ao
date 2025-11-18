<?php

namespace app\services\posts;

use yii\data\ActiveDataProvider;

class PostCounter
{
    private PostFinder $postFinder;

    public function __construct(
        PostFinder $postFinder
    ) {
        $this->postFinder = $postFinder;
    }

    public function getPostNumbersBatch(ActiveDataProvider $dataProvider): array
    {
        $posts = $dataProvider->getModels();

        if (empty($posts)) {
            return [];
        }

        $postIds = $this->extractPostIds($posts);
        $ipAddresses = $this->extractUniqueIpAddresses($posts);

        $allPosts = $this->postFinder->findPostsByIp($ipAddresses);

        return $this->calculatePostNumbers($allPosts, $postIds);
    }

    private function extractPostIds(array $posts): array
    {
        $postIds = [];

        foreach ($posts as $post) {
            $postIds[] = $post->id;
        }

        return $postIds;
    }

    private function extractUniqueIpAddresses(array $posts): array
    {
        $ipAddresses = [];

        foreach ($posts as $post) {
            $ipAddresses[] = $post->ip_address;
        }

        return array_unique($ipAddresses);
    }

    private function calculatePostNumbers(array $allPosts, array $targetPostIds): array
    {
        $postNumbers = [];
        $currentIp = null;
        $counter = 0;
        $targetPostIdsMap = array_flip($targetPostIds);

        foreach ($allPosts as $postData) {
            if ($currentIp !== $postData['ip_address']) {
                $currentIp = $postData['ip_address'];
                $counter = 1;
            } else {
                $counter++;
            }

            if (isset($targetPostIdsMap[$postData['id']])) {
                $postNumbers[$postData['id']] = $counter;
            }
        }

        return $postNumbers;
    }
}
