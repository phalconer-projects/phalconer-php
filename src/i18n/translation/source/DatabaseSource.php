<?php

namespace phalconer\i18n\translation\source;

use Phalcon\Translate\Adapter\Database;
use Phalcon\Db\Adapter;

class DatabaseSource extends AbstractSource
{
    /**
     * @var Adapter
     */
    protected $db;
    
    /**
     * The table that is storing the translations
     * @var string
     */
    protected $translationsTable;
    
    /**
     * If need formatting message using ICU MessageFormatter
     * @var bool
     */
    protected $useIcuMessageFormatter;
    
    /**
     * {@inheritdoc}
     */
    public function __construct(array $params = NULL)
    {
        parent::__construct($params);
        if ($params !== NULL) {
            $this->setDb(isset($params['db']) ? $params['db'] : NULL);
            $this->setTranslationsTable(isset($params['translationsTable']) ? $params['translationsTable'] : 'translations');
            $this->setUseIcuMessageFormatter(isset($params['useIcuMessageFormatter']) ? $params['useIcuMessageFormatter'] : true);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function makeAdapter($language)
    {
        return new Database([
            'db' => $this->db,
            'table' => $this->translationsTable,
            'language' => $language,
            'useIcuMessageFormatter' => $this->useIcuMessageFormatter,
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function add($language, $label, $translation)
    {
        $sql = "SELECT * FROM `$this->translationsTable` WHERE `language` = :language AND `key_name` = :key_name";
        $result = $this->db->query($sql, ['language' => $language, 'key_name' => $label]);
        
        if ($result->numRows() > 0) {
            return $this->db->update(
                $this->translationsTable,
                ['value'],
                [$translation],
                [
                    'conditions' => "language = ? AND key_name = ?",
                    'bind' => [$language, $label]
                ]
            );
        } else {
            return $this->db->insert($this->translationsTable, [$language, $label, $translation], ['language', 'key_name', 'value']);
        }
    }
    
    /**
     * @return Adapter
     */
    function getDb()
    {
        return $this->db;
    }

    /**
     * @param Adapter $db
     * @return DatabaseSource
     */
    function setDb(Adapter $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @return string
     */
    function getTranslationsTable()
    {
        return $this->translationsTable;
    }

    /**
     * @param string $translationsTable
     * @return DatabaseSource
     */
    function setTranslationsTable($translationsTable)
    {
        $this->translationsTable = $translationsTable;
        return $this;
    }

    /**
     * @return bool
     */
    function getUseIcuMessageFormatter()
    {
        return $this->useIcuMessageFormatter;
    }
    
    /**
     * @param bool $useIcuMessageFormatter
     * @return DatabaseSource
     */
    function setUseIcuMessageFormatter($useIcuMessageFormatter)
    {
        $this->useIcuMessageFormatter = $useIcuMessageFormatter;
        return $this;
    }
}
