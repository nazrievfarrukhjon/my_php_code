<?php

namespace App\Validations;
use DateTime;
use Exception;

readonly class BlacklistStoreValidation
{
    public function __construct(private array $params)
    {
    }

    /**
     * @throws Exception
     */
    public function check(): void
    {

        $fields = [
            'first_name' => 'first_name',
            'second_name' => 'second_name',
            'third_name' => 'Third Name',
            'fourth_name' => 'Fourth Name',
            'type' => 'Type',
            'birth_date' => 'Birth Date'
        ];
        foreach ($fields as $field => $label) {
            if ( $field !== 'birth_date' &&
                (
                    !isset($this->params[$field]) ||
                    !is_string($this->params[$field]) ||
                    preg_match('/[^a-zA-Zа-яА-Я]/', $this->params[$field])
                )
            ) {
                throw new Exception("$label is invalid", 422);
            } else

            if ($field === 'birth_date') {
                if (!$this->isValidDateFormat($this->params[$field])) {
                    throw new Exception("Invalid date format for $label", 422);
                }
            }
        }
    }

    /**
     * Check if a date string is in the format YYYY-MM-DD
     * @param string $dateString
     * @return bool
     */
    private function isValidDateFormat(string $dateString): bool
    {
        $format = 'Y-m-d';
        $dateTime = DateTime::createFromFormat($format, $dateString);
        return $dateTime && $dateTime->format($format) === $dateString;
    }

}