<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use RdKafka\Producer;

class KafkaProducer {
    private $producer;

    public function __construct() {
        // Configure the Kafka producer
        $this->producer = new Producer();
        $this->producer->setLogLevel(LOG_DEBUG);
        $this->producer->addBrokers('localhost:9092'); // Kafka broker address
    }

    public function sendNotification($topicName, $message) {
        // Create or access the topic
        $topic = $this->producer->newTopic($topicName);

        // Produce a message
        $topic->producev(RD_KAFKA_PARTITION_UA, 0, json_encode($message));

        // Trigger message delivery
        $this->producer->poll(0);

        // Ensure all messages are sent
        $result = $this->producer->flush(1000);
        if ($result !== RD_KAFKA_RESP_ERR_NO_ERROR) {
            log_message('error', 'Failed to flush messages: ' . rd_kafka_err2str($result));
        }
    }
}