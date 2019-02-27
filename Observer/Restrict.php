<?php
 
namespace OuterEdge\LoginGate\Observer;
 
use Magento\Customer\Model\Context;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
 
class Restrict implements ObserverInterface
{
    const TRADE_STORE_CORE = 'trade';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\App\Http\Context $context,
        \Magento\Framework\App\ActionFlag $actionFlag,
        StoreManagerInterface $storeManager
    )
    {
        $this->_response = $response;
        $this->_urlFactory = $urlFactory;
        $this->_context = $context;
        $this->_actionFlag = $actionFlag;
        $this->_storeManager = $storeManager;
    }
 
    /**
     * Get Store code
     *
     * @return string
     */
    public function getStoreCode() {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {

        if ($this->getStoreCode() !== self::TRADE_STORE_CORE) {
            return; 
        }

        $allowedRoutes = [
            'customer_account_login',
            'customer_account_loginpost',
            'customer_account_create',
            'customer_account_createpost',
            'customer_account_logoutsuccess',
            'customer_account_confirm',
            'customer_account_confirmation',
            'customer_account_forgotpassword',
            'customer_account_forgotpasswordpost',
            'customer_account_createpassword',
            'customer_account_resetpasswordpost',
            'customer_section_load'
        ];

        $allowedRequestUri = [
            'contact',
            'help',
            'rest',
            'sitemap.xml',
            'robots.txt'
        ];
 
        $request = $observer->getEvent()->getRequest();
        $isCustomerLoggedIn = $this->_context->getValue(Context::CONTEXT_AUTH);
        $actionFullName = strtolower($request->getFullActionName());
        $currentRequestUri = str_replace('/','',strtolower($request->getRequestUri()));
 
        if (!$isCustomerLoggedIn 
            && !in_array($actionFullName, $allowedRoutes) 
            && !in_array($currentRequestUri, $allowedRequestUri)) {
                
            $this->_response->setRedirect($this->_urlFactory->create()->getUrl('customer/account/login'));
        }
    }
}
