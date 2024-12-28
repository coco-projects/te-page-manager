<?php

    declare(strict_types = 1);

    namespace Coco\tePageManager\tables;

    use Coco\tableManager\TableAbstract;

    class Pages extends TableAbstract
    {
        public string $comment = 'telegraph 页面';

        public array $fieldsSqlMap = [
            "path"        => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',",
            "url"         => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '完整url',",
            "title"       => "`__FIELD__NAME__` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',",
            "author_name" => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',",
            "author_url"  => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户url',",
            "image_url"   => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面url',",
            "description" => "`__FIELD__NAME__` text COLLATE utf8mb4_unicode_ci COMMENT '描述',",
            "content"     => "`__FIELD__NAME__` text COLLATE utf8mb4_unicode_ci COMMENT '内容json',",
            "views"       => "`__FIELD__NAME__` int(10) unsigned NOT NULL COMMENT '查看次数',",
            "can_edit"    => "`__FIELD__NAME__` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '可编辑',",
            "account"     => "`__FIELD__NAME__` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '关联账号',",
            "token"       => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '对应页面的token',",
            "type"        => "`__FIELD__NAME__` int(10) unsigned NOT NULL COMMENT 'type为 2，3 时，对应的 collection 的 type 的 id',",
            "update_time" => "`__FIELD__NAME__` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',",
            "time"        => "`__FIELD__NAME__` INT (10) UNSIGNED NOT NULL DEFAULT '0',",
        ];

        protected array $indexSentence = [
            "account" => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
            "token"   => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
        ];

        public function setPathField(string $value): static
        {
            $this->setFeildName('path', $value);

            return $this;
        }

        public function getPathField(): string
        {
            return $this->getFieldName('path');
        }

        public function setUrlField(string $value): static
        {
            $this->setFeildName('url', $value);

            return $this;
        }

        public function getUrlField(): string
        {
            return $this->getFieldName('url');
        }

        public function setTitleField(string $value): static
        {
            $this->setFeildName('title', $value);

            return $this;
        }

        public function getTitleField(): string
        {
            return $this->getFieldName('title');
        }

        public function setAuthorNameField(string $value): static
        {
            $this->setFeildName('author_name', $value);

            return $this;
        }

        public function getAuthorNameField(): string
        {
            return $this->getFieldName('author_name');
        }

        public function setAuthorUrlField(string $value): static
        {
            $this->setFeildName('author_url', $value);

            return $this;
        }

        public function getAuthorUrlField(): string
        {
            return $this->getFieldName('author_url');
        }

        public function setImageUrlField(string $value): static
        {
            $this->setFeildName('image_url', $value);

            return $this;
        }

        public function getImageUrlField(): string
        {
            return $this->getFieldName('image_url');
        }

        public function setDescriptionField(string $value): static
        {
            $this->setFeildName('description', $value);

            return $this;
        }

        public function getDescriptionField(): string
        {
            return $this->getFieldName('description');
        }

        public function setContentField(string $value): static
        {
            $this->setFeildName('content', $value);

            return $this;
        }

        public function getContentField(): string
        {
            return $this->getFieldName('content');
        }

        public function setViewsField(string $value): static
        {
            $this->setFeildName('views', $value);

            return $this;
        }

        public function getViewsField(): string
        {
            return $this->getFieldName('views');
        }

        public function setCanEditField(string $value): static
        {
            $this->setFeildName('can_edit', $value);

            return $this;
        }

        public function getCanEditField(): string
        {
            return $this->getFieldName('can_edit');
        }

        public function setAccountField(string $value): static
        {
            $this->setFeildName('account', $value);

            return $this;
        }

        public function getAccountField(): string
        {
            return $this->getFieldName('account');
        }

        public function setTokenField(string $value): static
        {
            $this->setFeildName('token', $value);

            return $this;
        }

        public function getTokenField(): string
        {
            return $this->getFieldName('token');
        }

        public function setTypeField(string $value): static
        {
            $this->setFeildName('type', $value);

            return $this;
        }

        public function getTypeField(): string
        {
            return $this->getFieldName('type');
        }

        public function setUpdateTimeField(string $value): static
        {
            $this->setFeildName('update_time', $value);

            return $this;
        }

        public function getUpdateTimeField(): string
        {
            return $this->getFieldName('update_time');
        }

        public function setTimeField(string $value): static
        {
            $this->setFeildName('time', $value);

            return $this;
        }

        public function getTimeField(): string
        {
            return $this->getFieldName('time');
        }


    }