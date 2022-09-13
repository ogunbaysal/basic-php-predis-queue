<?php

class MailerTask extends BasicPhpPredisQueue\Task {
    private string $template = '';
    private string $email = '';
    private string $subject = '';
    private array $data = [];

    public function action()
    {
        var_dump([
            'template' => $this->template,
            'email' => $this->email,
            'subject' => $this->subject,
            'data' => $this->data,
        ]);
    }

    public function fromArray(array $array)
    {
        $this->template = $array['template'];
        $this->email = $array['email'];
        $this->subject = $array['subject'];
        $this->data = $array['data'];
    }

    public function toArray(): array
    {
        return [
            'template' => $this->template,
            'email' => $this->email,
            'subject' => $this->subject,
            'data' => $this->data,
        ];
    }
}