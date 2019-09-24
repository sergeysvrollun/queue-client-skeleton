<?php


namespace ReputationVIP\QueueClient\Adapter;


use Exception;
use ReputationVIP\QueueClient\Adapter\Exception\InvalidMessageException;
use ReputationVIP\QueueClient\Adapter\Exception\QueueAccessException;
use ReputationVIP\QueueClient\PriorityHandler\Priority\Priority;
use ReputationVIP\QueueClient\PriorityHandler\PriorityHandlerInterface;
use ReputationVIP\QueueClient\PriorityHandler\StandardPriorityHandler;
use InvalidArgumentException;
use UnexpectedValueException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Metadata\Source\Factory;

class DbAdapter extends AbstractAdapter implements AdapterInterface
{
    const MAX_NB_MESSAGES = 10;
    const MAX_TIME_IN_FLIGHT = 30;
    /** @var Adapter $db */
    private $db;

    /** @var PriorityHandlerInterface $priorityHandler */
    private $priorityHandler;

    /**
     * @param Adapter                  $db
     * @param PriorityHandlerInterface $priorityHandler
     *
     * @throws QueueAccessException
     */
    public function __construct(Adapter $db, PriorityHandlerInterface $priorityHandler = null)
    {
        if (null === $priorityHandler) {
            $priorityHandler = new StandardPriorityHandler();
        }

        $this->db = $db;
        $this->priorityHandler = $priorityHandler;
        return $this;
    }

    /**
     * @param string $queueName
     *
     * @return string
     */
    private function prepareTableName(string $queueName): string
    {
        return $queueName;
    }

    /**
     * @inheritdoc
     */
    public function listQueues($prefix = '')
    {
        $metadata = Factory::createSourceFromAdapter($this->db);
        return $metadata->getTableNames();
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     * @throws InvalidMessageException
     * @throws QueueAccessException
     */
    public function addMessage($queueName, $message, Priority $priority = null, $delaySeconds = 0)
    {
        if (empty($queueName)) {
            throw new InvalidArgumentException('Queue name empty or not defined.');
        }

        if (empty($message)) {
            throw new InvalidMessageException($message, 'Message empty or not defined.');
        }

        if (null === $priority) {
            $priority = $this->priorityHandler->getDefault();
        }

        if (!$this->isQueueExists($queueName)) {
            throw new QueueAccessException(
                "Queue " . $queueName
                . " doesn't exist, please create it before using it."
            );
        }
        $tableName = $this->prepareTableName($queueName);
        $new_message = [
            'id'             => uniqid(
                $queueName . $priority->getLevel(), true
            ),
            'priority_level' => $priority->getLevel(),
            'time_in_flight' => null,
            'delayed_until'  => date('Y-m-d H:i:s', time() + $delaySeconds),
            'body'           => serialize($message),
        ];
        $sql = "INSERT INTO $tableName (id, priority_level, time_in_flight, delayed_until, body) 
            VALUES ( :id, :priority_level, :time_in_flight, :delayed_until, :body)";
        $this->db->query($sql, $new_message);
        return $this;
    }

    /**
     * @param string $queueName
     *
     * @return bool
     */
    protected function isQueueExists($queueName)
    {
        if (empty($queueName)) {
            throw new InvalidArgumentException('Queue name empty or not defined.');
        }

        $tableName = $this->prepareTableName($queueName);
        $metadata = Factory::createSourceFromAdapter($this->db);
        $tableNames = $metadata->getTableNames();
        return in_array($tableName, $tableNames);
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     * @throws QueueAccessException
     */
    public function getMessages($queueName, $nbMsg = 1, Priority $priority = null)
    {
        if (empty($queueName)) {
            throw new InvalidArgumentException('Queue name empty or not defined.');
        }

        if (!is_numeric($nbMsg)) {
            throw new InvalidArgumentException('Number of messages must be numeric.');
        }

        if ($nbMsg <= 0 || $nbMsg > static::MAX_NB_MESSAGES) {
            throw new InvalidArgumentException('Number of messages is not valid.');
        }

        if (!$this->isQueueExists($queueName)) {

            throw new QueueAccessException(
                "Queue " . $queueName
                . " doesn't exist, please create it before using it."
            );
        }
        return ['not exists'];
        $tableName = $this->prepareTableName($queueName);
        //$table = new TableGateway($tableName, $this->db, new RowGatewayFeature('id'));
        $sql = "SELECT * FROM $tableName WHERE
            (((unix_timestamp(now()) - unix_timestamp(time_in_flight)) > "
            . self::MAX_TIME_IN_FLIGHT . " OR time_in_flight IS NULL)
            AND unix_timestamp(delayed_until) <= unix_timestamp(now())) ";
        if (null !== $priority) {
            $sql .= " AND priority_level = " . $priority->getLevel() . " ";
        }
        if ($nbMsg) {
            $sql .= " LIMIT $nbMsg";
        }

        $rows = $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE);

        print_r($rows);
        $messages = [];
        foreach ($rows as $message) {
            $message['time_in_flight'] = time();
            $message['body'] = unserialize($message['body']);
            $message['priority'] = $message['priority_level'];
            $messages[] = $message;
        }
        return $messages;
    }

    /**
     * @inheritdoc
     *
     * @param string $queueName
     * @param array  $message
     *
     * @return $this|AdapterInterface
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidMessageException
     * @throws QueueAccessException
     */
    public function deleteMessage($queueName, $message)
    {
        if (empty($queueName)) {
            throw new InvalidArgumentException('Queue name empty or not defined.');
        }

        if (empty($message)) {
            throw new InvalidMessageException($message, 'Message empty or not defined.');
        }

        if (!is_array($message)) {
            throw new InvalidMessageException($message, 'Message must be an array.');
        }

        if (!isset($message['id'])) {
            throw new InvalidMessageException($message, 'Message id not found in message.');
        }

        if (!isset($message['priority'])) {
            throw new InvalidMessageException($message, 'Message priority not found in message.');
        }

        $priority
            = $this->priorityHandler->getPriorityByLevel($message['priority']);

        if (!$this->isQueueExists($queueName)) {
            throw new QueueAccessException(
                "Queue " . $queueName
                . " doesn't exist, please create it before use it."
            );
        }

        $tableName = $this->prepareTableName($queueName);
        $sql = "DELETE FROM $tableName WHERE id = :id ";
        $params = ['id' => $message['id']];
        if (null !== $priority) {
            $params[':priority_level'] = $priority->getLevel();
            $sql .= " AND priority_level = :priority_level";
        }

        $this->db->query($sql, $params)->execute();
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     * @throws QueueAccessException
     * @throws UnexpectedValueException
     */
    public function isEmpty($queueName, Priority $priority = null)
    {
        if (empty($queueName)) {
            throw new InvalidArgumentException('Queue name empty or not defined.');
        }

        if (!$this->isQueueExists($queueName)) {
            throw new QueueAccessException(
                "Queue " . $queueName
                . " doesn't exist, please create it before using it."
            );
        }
        $tableName = $this->prepareTableName($queueName);
        $sql = "SELECT count(*) FROM $tableName ";
        $params = [];
        if (null !== $priority) {
            $params[':priority_level'] = $priority->getLevel();
            $sql .= " AND priority_level = :priority_level";
        }
        $count = $this->db->query($sql, $params);
        return $count > 0 ? false : true;
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     * @throws QueueAccessException
     * @throws UnexpectedValueException
     */
    public function getNumberMessages($queueName, Priority $priority = null)
    {
        if (empty($queueName)) {
            throw new InvalidArgumentException('Queue name empty or not defined.');
        }

        if (!$this->isQueueExists($queueName)) {
            throw new QueueAccessException(
                "Queue " . $queueName
                . " doesn't exist, please create it before using it."
            );
        }

        $tableName = $this->prepareTableName($queueName);
        $sql = "SELECT count(*) FROM $tableName WHERE 
            ((unix_timestamp(now()) - unix_timestamp(time_in_flight)) > :max_time_in_flight || time_in_flight IS NULL) ";
        $params = ['max_time_in_flight' => self::MAX_TIME_IN_FLIGHT];
        if (null !== $priority) {
            $params[':priority_level'] = $priority->getLevel();
            $sql .= "AND priority_level = :priority_level";
        }

        return $this->db->query($sql, $params);
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    public function deleteQueue($queueName)
    {
        if (empty($queueName)) {
            throw new InvalidArgumentException('Queue name empty or not defined.');
        }

//        if (!$this->isQueueExists($queueName)) {
//            throw new QueueAccessException(
//                "Queue " . $queueName
//                . " doesn't exist, please create it before using it."
//            );
//        }

        $tableName = $this->prepareTableName($queueName);
        $this->db->query("DROP TABLE IF EXISTS $tableName", Adapter::QUERY_MODE_EXECUTE);
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    public function createQueue($queueName)
    {
        if (empty($queueName)) {
            throw new InvalidArgumentException('Queue name empty or not defined.');
        }
        if (strpos($queueName, ' ') !== false) {
            throw new InvalidArgumentException('Queue name must not contain white spaces.');
        }

        if ($this->isQueueExists($queueName)) {
            throw new QueueAccessException(
                'A queue named ' . $queueName . ' already exist.'
            );
        }
        $tableName = $this->prepareTableName($queueName);
        $sql = "CREATE TABLE $tableName (
            `id` VARCHAR(45) NOT NULL, 
            `priority_level` int(11) NOT NULL, 
            `body` text COLLATE utf8_unicode_ci,
            `time_in_flight` datetime DEFAULT NULL,
            `delayed_until` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    public function renameQueue($sourceQueueName, $targetQueueName)
    {
        if (empty($sourceQueueName)) {
            throw new InvalidArgumentException('Source queue name empty or not defined.');
        }

        if (empty($targetQueueName)) {
            throw new InvalidArgumentException('Target queue name empty or not defined.');
        }

        $sourceTableName = $this->prepareTableName($sourceQueueName);
        $targetTableName = $this->prepareTableName($targetQueueName);
        $sql = "ALTER TABLE $sourceTableName RENAME TO $targetTableName;";
        $this->db->query($sql);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     * @throws QueueAccessException
     * @throws UnexpectedValueException
     */
    public function purgeQueue($queueName, Priority $priority = null)
    {
        if (empty($queueName)) {
            throw new InvalidArgumentException('Queue name empty or not defined.');
        }

        if (null === $priority) {
            $priorities = $this->priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $this->purgeQueue($queueName, $priority);
            }

            return $this;
        }

        if (!$this->isQueueExists($queueName)) {
            throw new QueueAccessException(
                "Queue " . $queueName
                . " doesn't exist, please create it before using it."
            );
        }
        $tableName = $this->prepareTableName($queueName);
        $this->db->query("DELETE FROM $tableName WHERE priority_level = :priority_level", [':priority_level' => $priority->getLevel()]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriorityHandler()
    {
        return $this->priorityHandler;
    }
}