<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Model\Customer;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\MailException;

/**
 * Customer repository.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerRepository implements \Magestore\Webpos\Api\Customer\CustomerRepositoryInterface
{
    /**
     * @var PsrLogger
     */
    protected $_logger;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Data\CustomerSecureFactory
     */
    protected $customerSecureFactory;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Customer\Model\ResourceModel\AddressRepository
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResourceModel;

    /**
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     */
    protected $customerMetadata;

    /**
     * @var \Magento\Customer\Api\Data\CustomerSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var ImageProcessorInterface
     */
    protected $imageProcessor;
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var
     */
    protected $_customerModel;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * CustomerRepository constructor.
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Data\CustomerSecureFactory $customerSecureFactory
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Model\ResourceModel\AddressRepository $addressRepository
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel
     * @param CustomerMetadataInterface $customerMetadata
     * @param \Magestore\Webpos\Api\Data\Customer\CustomerSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param DataObjectHelper $dataObjectHelper
     * @param ImageProcessorInterface $imageProcessor
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Model\AccountManagement $accountManagement
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Data\CustomerSecureFactory $customerSecureFactory,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Customer\Model\ResourceModel\AddressRepository $addressRepository,
        \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel,
        \Magento\Customer\Api\CustomerMetadataInterface $customerMetadata,
        \Magestore\Webpos\Api\Data\Customer\CustomerSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        DataObjectHelper $dataObjectHelper,
        ImageProcessorInterface $imageProcessor,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Model\Customer $customerModel,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerFactory = $customerFactory;
        $this->customerSecureFactory = $customerSecureFactory;
        $this->customerRegistry = $customerRegistry;
        $this->addressRepository = $addressRepository;
        $this->customerResourceModel = $customerResourceModel;
        $this->customerMetadata = $customerMetadata;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->imageProcessor = $imageProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_customerModel = $customerModel;
        $this->_logger = $logger;
    }


    /**
     * {@inheritdoc}
     */
    public function getById($customerId)
    {
        $customerModel = $this->customerRegistry->retrieve($customerId);
        $customerData = $customerModel->getDataModel();
        $customerData->setFullName($customerData->getFirstname().' '.$customerData->getLastname());
        $addresses = $customerModel->getAddresses();
        $telephone = 'N/A';
        foreach ($addresses as $address) {
            $telephone = $address->getData('telephone');
            if ($telephone != '') {
                break;
            }
        }
        $customerData->setTelephone($telephone);
        return $customerData;
    }

    
    /**
     * Validate customer attribute values.
     *
     * @param \Magestore\Webpos\Api\Data\Customer\CustomerInterface $customer
     * @throws InputException
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function validate(\Magestore\Webpos\Api\Data\Customer\CustomerInterface $customer)
    {
        $webposException = new InputException();
        if (!\Zend_Validate::is(trim($customer->getFirstname()), 'NotEmpty')) {
            $webposException->addError(__(InputException::REQUIRED_FIELD, ['fieldName' => 'firstname']));
        }

        if (!\Zend_Validate::is(trim($customer->getLastname()), 'NotEmpty')) {
            $webposException->addError(__(InputException::REQUIRED_FIELD, ['fieldName' => 'lastname']));
        }

        $isEmailAddress = \Zend_Validate::is(
            $customer->getEmail(),
            'EmailAddress'
        );

        if (!$isEmailAddress) {
            $webposException->addError(
                __(
                    InputException::INVALID_FIELD_VALUE,
                    ['fieldName' => 'email', 'value' => $customer->getEmail()]
                )
            );
        }

        $dob = $this->getAttributeMetadata('dob');
        if ($dob !== null && $dob->isRequired() && '' == trim($customer->getDob())) {
            $webposException->addError(__(InputException::REQUIRED_FIELD, ['fieldName' => 'dob']));
        }

        $taxvat = $this->getAttributeMetadata('taxvat');
        if ($taxvat !== null && $taxvat->isRequired() && '' == trim($customer->getTaxvat())) {
            $webposException->addError(__(InputException::REQUIRED_FIELD, ['fieldName' => 'taxvat']));
        }

        $gender = $this->getAttributeMetadata('gender');
        if ($gender !== null && $gender->isRequired() && '' == trim($customer->getGender())) {
            $webposException->addError(__(InputException::REQUIRED_FIELD, ['fieldName' => 'gender']));
        }

        if ($webposException->wasErrorAdded()) {
            throw $webposException;
        }
    }

    /**
     * Get attribute metadata.
     *
     * @param string $attributeCode
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface|null
     */
    private function getAttributeMetadata($attributeCode)
    {
        try {
            return $this->customerMetadata->getAttributeMetadata($attributeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($email, $websiteId = null)
    {
        $customerModel = $this->customerRegistry->retrieveByEmail($email, $websiteId);
        $customerData = $customerModel->getDataModel();
        $customerData->setFullName($customerData->getFirstname().' '.$customerData->getLastname());
        $addresses = $customerModel->getAddresses();
        $telephone = 'N/A';
        foreach ($addresses as $address) {
            $telephone = $address->getData('telephone');
            if ($telephone != '') {
                break;
            }
        }
        $customerData->setTelephone($telephone);
        return $customerData;
    }

    /**
     * @param $email
     */
    public function addSubscriber($email)
    {
        if ($email) {
            $subscriberModel = $this->_subscriberFactory->create()->loadByEmail($email);
            if ($subscriberModel->getId() === NULL) {
                try {
                    $this->_subscriberFactory->create()->subscribe($email);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_logger->critical($e);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }

            } elseif ($subscriberModel->getData('subscriber_status') != 1) {
                $subscriberModel->setData('subscriber_status', 1);
                try {
                    $subscriberModel->save();
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        }
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(\Magestore\Webpos\Api\Data\Customer\CustomerInterface $customer, $passwordHash = null)
    {

        $this->validate($customer);

        $isSubscriber = $customer->getSubscriberStatus();
        if ($isSubscriber) {
            $email = $customer->getEmail();
            $this->addSubscriber($email);
        }

        if ($customer->getId() && is_numeric($customer->getId())) {
            $status = "edit";
        } else {
            $status = "new";
        }


        $prevCustomerData = null;

        if ($customer->getId() && is_numeric($customer->getId())) {
            $prevCustomerData = $this->getById($customer->getId());
        }


        $customer = $this->imageProcessor->save(
            $customer,
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $prevCustomerData
        );

        $origAddresses = $customer->getAddresses();
        $customer->setAddresses([]);
        $customerData = $this->extensibleDataObjectConverter->toNestedArray(
            $customer,
            [],
            '\Magestore\Webpos\Api\Data\Customer\CustomerInterface'
        );

        $customer->setAddresses($origAddresses);
        $webposCustomerModel = $this->customerFactory->create(['data' => $customerData]);
        $storeId = $webposCustomerModel->getStoreId();
        if ($storeId === null) {
            $webposCustomerModel->setStoreId($this->storeManager->getStore()->getId());
        }
        $webposCustomerModel->setId($customer->getId());


        // Need to use attribute set or future updates can cause data loss
        if (!$webposCustomerModel->getAttributeSetId()) {
            $webposCustomerModel->setAttributeSetId(
                \Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER
            );
        }

        // Populate model with secure data
        if ($customer->getId() && is_numeric($customer->getId())) {
            $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
            $webposCustomerModel->setRpToken($customerSecure->getRpToken());
            $webposCustomerModel->setRpTokenCreatedAt($customerSecure->getRpTokenCreatedAt());
            $webposCustomerModel->setPasswordHash($customerSecure->getPasswordHash());
        } else {
            if ($passwordHash) {
                $webposCustomerModel->setPasswordHash($passwordHash);
            }
        }


        // If customer email was changed, reset RpToken info
        if ($prevCustomerData
            && $prevCustomerData->getEmail() !== $webposCustomerModel->getEmail()
        ) {
            $webposCustomerModel->setRpToken(null);
            $webposCustomerModel->setRpTokenCreatedAt(null);
        }

        if (!$webposCustomerModel->getWebsiteId()) {
            $webposCustomerModel->setWebsiteId($this->storeManager->getWebsite()->getId());
        }


        $webposCustomerModel->save();


        $this->customerRegistry->push($webposCustomerModel);

        $customerId = $webposCustomerModel->getId();

        if ($customer->getAddresses() !== null) {
            if ($customer->getId() && is_numeric($customer->getId())) {
                $existingAddresses = $this->getById($customer->getId())->getAddresses();
                $getIdFunc = function ($address) {
                    return $address->getId();
                };
                $existingAddressIds = array_map($getIdFunc, $existingAddresses);
            } else {
                $existingAddressIds = [];
            }

            $savedAddressIds = [];
            foreach ($customer->getAddresses() as $address) {
                $address->setCustomerId($customerId)
                    ->setRegion($address->getRegion());
                $this->addressRepository->save($address);
                if ($address->getId()) {
                    $savedAddressIds[] = $address->getId();
                }
            }

            $addressIdsToDelete = array_diff($existingAddressIds, $savedAddressIds);
            foreach ($addressIdsToDelete as $addressId) {
                $this->addressRepository->deleteById($addressId);
            }
        }


        $savedCustomer = $this->get($customer->getEmail(), $customer->getWebsiteId());
        $this->eventManager->dispatch(
            'customer_save_after_data_object',
            ['customer_data_object' => $savedCustomer, 'orig_customer_data_object' => $customer]
        );

        if ($status == 'new') {
            $webposCustomerModel = $this->_customerModel->load($savedCustomer->getId());
            try {
                $webposCustomerModel->sendNewAccountEmail();
            } catch (MailException $e) {
                // If we are not able to send a new account email, this should be ignored
                $this->_logger->critical($e);
            }

        }
        return $savedCustomer;
    }


    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $webposCollection */
        $webposCollection = $this->customerFactory->create()->getCollection();
        $this->extensionAttributesJoinProcessor->process($webposCollection, 'Magestore\Webpos\Api\Data\Customer\CustomerInterface');
        // This is needed to make sure all the attributes are properly loaded
        foreach ($this->customerMetadata->getAllAttributesMetadata() as $metadata) {
            $webposCollection->addAttributeToSelect($metadata->getAttributeCode());
        }
        // Needed to enable filtering on name as a whole
        $webposCollection->addNameToSelect();
        // Needed to enable filtering based on billing address attributes
        $webposCollection->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->getSelect()
            ->joinLeft(
                ['ns' => $webposCollection->getTable('newsletter_subscriber')],
                'e.entity_id = ns.customer_id',
                ['ns.subscriber_status']
            )
            ->columns('IFNULL(at_billing_telephone.telephone,"N/A") AS telephone')
            ->columns('CONCAT(e.firstname, " ", e.lastname) AS full_name')
            ->columns('IFNULL(ns.subscriber_status,"0") AS subscriber_status');
        ;
        ;
        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $webposCollection);
        }
        $searchResults->setTotalCount($webposCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $webposCollection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $webposCollection->setCurPage($searchCriteria->getCurrentPage());
        $webposCollection->setPageSize($searchCriteria->getPageSize());
        $customers = [];

        /** @var \Magento\Customer\Model\Customer $customerModel */
        foreach ($webposCollection as $customerModel) {
            $customers[] = $customerModel->getDataModel();
        }
        if(($searchCriteria->getCurrentPage() > 1) && ($searchCriteria->getPageSize() >= $searchResults->getTotalCount())){
            $customers = [];
        }
        $searchResults->setItems($customers);
        
        $this->eventManager->dispatch(
            'webpos_api_customer_list_after', ['search_results' => $searchResults]
        );        
        
        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $webposCondition = $filter->getConditionType() ? $filter->getConditionType() : 'like';
            $fieldName = $filter->getField();
            if(in_array($fieldName, ['full_name','telephone','email'])) {
                switch ($webposCondition) {
                    case 'eq':
                        $webposCondition = '=';
                        break;
                    case 'neq':
                        $webposCondition = '!=';
                        break;
                }
            }
            if ($fieldName == 'full_name') {
                $fieldName = 'CONCAT(e.firstname, " ", e.lastname)';
                $collection->getSelect()->orWhere($fieldName.' '.$webposCondition.' "'. $filter->getValue().'"');
            } else if ($fieldName == 'telephone') {
                $fieldName = 'IFNULL(at_billing_telephone.telephone,"N/A")';
                $collection->getSelect()->orWhere($fieldName.' '.$webposCondition.' "'. $filter->getValue().'"');
            }else if ($fieldName == 'email') {
                $collection->getSelect()->orWhere($fieldName.' '.$webposCondition.' "'. $filter->getValue().'"');
            } else {
                $fields[] = ['attribute' => $fieldName, $webposCondition => $filter->getValue()];
            }
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }
}
