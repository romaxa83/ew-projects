<?php

namespace WezomCms\AmoCrm\Services;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Collections\TagsCollection;
use AmoCRM\Collections\UsersCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Filters\ContactsFilter;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\AccountModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\BaseCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\CategoryCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\CheckboxCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\MultiselectCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\NumericCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\UrlCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\CategoryCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\CheckboxCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultiselectCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NullCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NumericCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\SelectCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\UrlCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\BaseEnumCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\CheckboxCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\NumericCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\UrlCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\NoteType\BaseServiceMessageNote;
use AmoCRM\Models\NoteType\CommonNote;
use AmoCRM\Models\TagModel;
use AmoCRM\Models\TaskModel;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use WezomCms\Core\Contracts\SettingsInterface;
use WezomCms\Core\Models\Setting;

class AmoCrmService
{
    protected AmoCRMApiClient $client;

    protected Collection $statuses;

    protected Collection $mapping;

    public function __construct()
    {
        $this->client = self::createClient();

        $accessToken = new AccessToken(
            [
                'access_token' => settings('amo-crm.connection.access_token'),
                'refresh_token' => settings('amo-crm.connection.refresh_token'),
                'expires' => settings('amo-crm.connection.expires')
            ]
        );

        $this->client->setAccessToken($accessToken)
            ->setAccountBaseDomain(config('cms.amo-crm.amo-crm.sub_domain'))
            ->onAccessTokenRefresh(
                function (AccessTokenInterface $accessToken, string $subDomain) {
                    self::updateConnectionSettings(
                        [
                            'sub_domain' => $subDomain,
                            'access_token' => $accessToken->getToken(),
                            'refresh_token' => $accessToken->getRefreshToken(),
                            'expires' => $accessToken->getExpires(),
                        ]
                    );
                }
            );

        $this->statuses = collect(config('cms.amo-crm.amo-crm.statuses', []));
        $this->mapping = collect(config('cms.amo-crm.amo-crm.fields_mapping', []));
    }

    public static function createClient(): AmoCRMApiClient
    {
        $config = config('cms.amo-crm.amo-crm');

        if (!$subDomain = array_get($config, 'sub_domain', '')) {
            throw new Exception('AmoCRM subdomain is not set');
        }
        if (!$clientId = array_get($config, 'client_id')) {
            throw new Exception('Integration id is not set');
        }
        if (!$clientSecret = array_get($config, 'client_secret')) {
            throw new Exception('Integration secret is not set');
        }

        $client = new AmoCRMApiClient(
            $clientId,
            $clientSecret,
            array_get($config, 'redirect_uri', null)
        );
        $client->setAccountBaseDomain($subDomain);

        return $client;
    }

    public static function updateConnectionSettings(array $fields): array
    {
        $settings = settings();

        foreach (['sub_domain', 'token_type', 'access_token', 'refresh_token', 'expires'] as $name) {
            if (array_has($fields, $name)) {
                $value = $fields[$name];

                Setting::updateOrCreate(
                    [
                        'module' => 'amo-crm',
                        'group' => 'connection',
                        'key' => $name,
                        'type' => 'input'
                    ],
                    [
                        'value' => $value
                    ]
                );

                $settings->set('amo-crm.connection.' . $name, $value);
            }
        }

        App::forgetInstance(SettingsInterface::class);

        return settings('amo-crm.connection');
    }

    public function addLead(
        string $name,
        string $status,
        float  $price = 0,
        array  $fields = [],
        int    $responsibleUserId = null,
        string $tag = null
    ): int
    {
        $responsibleUserId = $responsibleUserId ?: settings('amo-crm.settings.responsible_user_id');

        $lead = new LeadModel();
        $lead->setName($name);
        $lead->setPrice($price);
        $lead->setStatusId($this->statuses->get($status));

        if ($tag) {
            $lead->setTags(
                (new TagsCollection())->add(
                    (new TagModel())
                        ->setName($tag)
                )
            );
        }

        if ($responsibleUserId) {
            $lead->setResponsibleUserId($responsibleUserId);
        }
//        if (!isset($fields['source'])) {
//            $fields['source'] = 'from_website';
//        }

        $lead->setCustomFieldsValues($this->buildFiledCollection($fields));


        $result = $this->client->leads()->addOne($lead);

        return $result->getId();
    }

    protected function buildFiledCollection(array $fields = [])
    {
        $customFieldsValues = new CustomFieldsValuesCollection();

        if ($fields) {
            foreach ($fields as $field => $value) {
                $map = $this->getFieldByName($field);

                if (($enumId = $map['enum_id'] ?? null) && ($enum = $map['enum'] ?? null)) {
                    $multitextCustomFieldValuesModel = new MultitextCustomFieldValuesModel();
                    $multitextCustomFieldValuesModel->setFieldId($map['id']);
                    $multitextCustomFieldValuesModel->setValues(
                        (new MultitextCustomFieldValueCollection())
                            ->add(
                                (new MultitextCustomFieldValueModel())
                                    ->setEnum($enum)
                                    ->setEnumId($enumId)
                                    ->setValue($value)
                            )
                    );
                    $customFieldsValues->add($multitextCustomFieldValuesModel);
                } elseif (($values = $map['values'] ?? null)) {
                    $selectCustomFieldValueModel = new MultiselectCustomFieldValuesModel();
                    $selectCustomFieldValueModel->setFieldId($map['id']);
                    if (is_array($value)) {
                        $collection = (new MultiselectCustomFieldValueCollection());
                        foreach ($value as $valueItem) {
                            $selectCustomFieldValueModel->setValues(
                                $collection->add(
                                    (new BaseEnumCustomFieldValueModel())
                                        ->setEnumId($valueItem)
                                )
                            );
                        }
                        if (empty($value)) {
                            $selectCustomFieldValueModel->setValues(
                                $collection->add(
                                    (new BaseEnumCustomFieldValueModel())
                                        ->setEnumId(null)
                                )
                            );
                        }
                    } else {
                        $selectCustomFieldValueModel->setValues(
                            (new SelectCustomFieldValueCollection())
                                ->add(
                                    (new BaseEnumCustomFieldValueModel())
                                        ->setEnumId(array_get($values, $value, $value))
                                )
                        );
                    }

                    $customFieldsValues->add($selectCustomFieldValueModel);
                } else {
                    if ($customField = $this->makeCustomField($map['id'], $value)) {
                        $customFieldsValues->add($customField);
                    }
                }
            }
        }

        return $customFieldsValues;
    }

    protected function getFieldByName(string $field): array
    {
        if (is_numeric($field)) {
            return ['id' => $field];
        }

        /** @var array $map */
        $map = $this->mapping->get($field);

        if (is_null($map)) {
            throw new Exception(__('cms-amo-crm::admin.:name is unmapped field', ['name' => $field]));
        }

        return $map;
    }

    protected function makeCustomField($id, $value)
    {
        $field = $this->getFieldByName('product_category');

        if ($id == $field['id']) {
            $customFieldValueModel = new CategoryCustomFieldValuesModel();
            $customFieldValueModel->setFieldId($id);
            $customFieldValueModel->setValues(
                (new CategoryCustomFieldValueCollection())
                    ->add(
                        (new BaseEnumCustomFieldValueModel())
                            ->setEnumId($value)
                    )
            );
            return $customFieldValueModel;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
            $customFieldValueModel = new UrlCustomFieldValuesModel();
            $customFieldValueModel->setFieldId($id);
            $customFieldValueModel->setValues(
                (new UrlCustomFieldValueCollection())
                    ->add(
                        (new UrlCustomFieldValueModel())
                            ->setValue($value)
                    )
            );
            return $customFieldValueModel;
        }

        if (is_string($value)) {
            $customFieldValueModel = new TextCustomFieldValuesModel();
            $customFieldValueModel->setFieldId($id);
            $customFieldValueModel->setValues(
                (new TextCustomFieldValueCollection())
                    ->add(
                        (new TextCustomFieldValueModel())
                            ->setValue($value)
                    )
            );
            return $customFieldValueModel;
        }

        if (is_bool($value)) {
            $customFieldValueModel = new CheckboxCustomFieldValuesModel();
            $customFieldValueModel->setFieldId($id);
            $customFieldValueModel->setValues(
                (new CheckboxCustomFieldValueCollection())
                    ->add(
                        (new CheckboxCustomFieldValueModel())
                            ->setValue($value)
                    )
            );
            return $customFieldValueModel;
        }

        if (is_numeric($value)) {
            $customFieldValueModel = new NumericCustomFieldValuesModel();
            $customFieldValueModel->setFieldId($id);
            $customFieldValueModel->setValues(
                (new NumericCustomFieldValueCollection())
                    ->add(
                        (new NumericCustomFieldValueModel())
                            ->setValue($value)
                    )
            );
            return $customFieldValueModel;
        }

        if (is_null($value)) {
            $customFieldValueModel = new BaseCustomFieldValuesModel();
            $customFieldValueModel->setFieldId($id);
            $customFieldValueModel->setValues(new NullCustomFieldValueCollection());
            return $customFieldValueModel;
        }

        return null;
    }

    public function addNoteToLead(int $id, string $text, string $noteType = null): int
    {
        $note = $noteType ? new $noteType() : new CommonNote();

        $note->setEntityId($id)->setText($text);

        if ($note instanceof BaseServiceMessageNote) {
            $note->setService(config('app.name'));
        }

        $result = $this->client->notes(EntityTypesInterface::LEADS)->addOne($note);

        return $result->getId();
    }

    /**
     * @return AccountModel|null
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getAccountInfo(): ?AccountModel
    {
        return $this->client->account()->getCurrent(
            [
                AccountModel::AMOJO_ID,
                AccountModel::AMOJO_RIGHTS,
                AccountModel::UUID,
                AccountModel::VERSION
            ]
        );
    }

    /**
     * @return UsersCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getUsers(): UsersCollection
    {
        return $this->client->users()->get();
    }

    public function addTask(int $elementId, string $elementType, string $text, int $responsibleUserId = null): int
    {
        $responsibleUserId = $responsibleUserId ?: settings('amo-crm.settings.responsible_user_id');

        if ($completeTill = settings('amo-crm.settings.complete_till')) {
            $completeTillTimestamp = Carbon::parse($completeTill)->timestamp;
        } else {
            $completeTillTimestamp = Carbon::now()->endOfDay()->timestamp;
        }

        $task = new TaskModel();
        $task->setTaskTypeId(TaskModel::TASK_TYPE_ID_CALL)
            ->setText($text)
            ->setCompleteTill($completeTillTimestamp)
            ->setEntityType($elementType)
            ->setEntityId($elementId);
        if ($responsibleUserId) {
            $task->setResponsibleUserId($responsibleUserId);
        }

        $result = $this->client->tasks()->addOne($task);

        return $result->getId();
    }

    /**
     * @param string|null $search
     * @return ContactsCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getContacts(string $search = null): ContactsCollection
    {
        $filter = new ContactsFilter();
        if ($search) {
            $filter->setQuery($search);
        }

        try {
            return $this->client->contacts()->get($filter);
        } catch (AmoCRMApiNoContentException $e) {
            return new ContactsCollection();
        }
    }

    /**
     * @param string $name
     * @param array $fields
     * @return int
     * @throws AmoCRMApiException
     * @throws Exception
     * @throws AmoCRMoAuthApiException
     */
    public function addContact(string $name, array $fields = []): int
    {
        $contact = new ContactModel();
        $contact->setName($name);
        $contact->setCustomFieldsValues($this->buildFiledCollection($fields));
        $result = $this->client->contacts()->addOne($contact);

        return $result->getId();
    }

    /**
     * @param int $id
     * @return ContactModel|null
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getContactById(int $id): ?ContactModel
    {
        try {
            return $this->client->contacts()->getOne($id);
        } catch (AmoCRMApiNoContentException $e) {
            return null;
        }
    }

    /**
     * @param ContactModel $contact
     * @param array $fields
     * @return int
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function updateContact(ContactModel $contact, array $fields = []): int
    {
        $updateCustomFields = $this->buildFiledCollection($fields);

        $customFields = $contact->getCustomFieldsValues();
        foreach ($fields as $field => $value) {
            $map = $this->getFieldByName($field);

            $updateCustomField = $updateCustomFields->getBy('fieldId', $map['id']);

            if ($customField = $customFields->getBy('fieldId', $map['id'])) {
                $customFields->replaceBy('fieldId', $map['id'], $updateCustomField);
            } else {
                $customFields->add($updateCustomField);
            }
        }

        $result = $this->client->contacts()->updateOne($contact);

        return $result->getId();
    }

    /**
     * @param int $id
     * @return LeadModel|null
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getLeadById(int $id): ?LeadModel
    {
        try {
            return $this->client->leads()->getOne($id);
        } catch (AmoCRMApiNoContentException $e) {
            return null;
        }
    }

    /**
     * @param LeadModel $lead
     * @param LinksCollection $linksCollection
     */
    public function linkToLead(LeadModel $lead, LinksCollection $linksCollection): void
    {
        $this->client->leads()->link($lead, $linksCollection);
    }

    /**
     * @param LeadModel $lead
     * @param array $fields
     * @return LeadModel
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function updateLead(LeadModel $lead, array $fields = []): LeadModel
    {
        $updateCustomFields = $this->buildFiledCollection($fields);

        $customFields = $lead->getCustomFieldsValues();
        foreach ($fields as $field => $value) {
            $map = $this->getFieldByName($field);

            $updateCustomField = $updateCustomFields->getBy('fieldId', $map['id']);

            if ($customField = $customFields->getBy('fieldId', $map['id'])) {
                $customFields->replaceBy('fieldId', $map['id'], $updateCustomField);
            } else {
                $customFields->add($updateCustomField);
            }
        }

        return $this->client->leads()->updateOne($lead);
    }

    /**
     * @param LeadModel $lead
     * @param float $price
     * @throws Exception
     */
    public function updateLeadStausId(LeadModel $lead, string $status_id)
    {
        $lead->setStatusId($status_id);
        return $this->client->leads()->updateOne($lead);
    }
}
