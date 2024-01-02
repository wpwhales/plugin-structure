<?php

namespace WPWhales\Contracts\Mail;

interface Attachable
{
    /**
     * Get an attachment instance for this entity.
     *
     * @return \WPWhales\Mail\Attachment
     */
    public function toMailAttachment();
}
