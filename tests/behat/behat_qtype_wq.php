<?php

use Behat\Behat\Context\Step\Given;
use Behat\Behat\Context\Step\When;
use Behat\Behat\Context\Step\Then;
use Behat\Gherkin\Node\TableNode;

/**
 * Steps definitions related to qtype_wq.
 */
class behat_qtype_wq extends behat_base {

    /**
     * @Given I open the action menu for :elementtype :identifier
     */
    public function i_open_the_action_menu_for($elementtype, $identifier) {
        // Find the action menu button for the specified element
        $xpath = "//div[contains(@class, '{$elementtype}') and contains(., '{$identifier}')]//button[contains(@class, 'action-menu-trigger')]";
        
        try {
            $this->execute('behat_general::i_click_on', [$xpath, 'xpath_element']);
        } catch (Exception $e) {
            // Provide verbose error information
            if (getenv('BEHAT_DEBUG')) {
                $session = $this->getSession();
                $currentUrl = $session->getCurrentUrl();
                $pageContent = $session->getPage()->getContent();
                $allMenus = $session->getPage()->findAll('css', '[class*="action-menu"]');
                
                $errorMsg = "Failed to open action menu for {$elementtype} '{$identifier}'.\n";
                $errorMsg .= "Current URL: {$currentUrl}\n";
                $errorMsg .= "XPath used: {$xpath}\n";
                $errorMsg .= "Found " . count($allMenus) . " elements with 'action-menu' in class name.\n";
                $errorMsg .= "Original error: " . $e->getMessage() . "\n";
                $errorMsg .= "Full page content:\n" . $pageContent . "\n";
                
                throw new Exception($errorMsg);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @Then I should see :count elements matching :selector
     */
    public function i_should_see_elements_matching($count, $selector) {
        // Convert count to integer
        $expectedcount = (int)$count;
        
        // Find elements matching the selector
        $elements = $this->find_all('css_element', $selector);
        
        if (count($elements) !== $expectedcount) {
            $session = $this->getSession();
            $currentUrl = $session->getCurrentUrl();
            
            $errorMsg = "Expected {$expectedcount} elements matching '{$selector}', but found " . count($elements);
            $errorMsg .= "\nCurrent URL: {$currentUrl}";
            
            if (getenv('BEHAT_DEBUG')) {
                $pageContent = $session->getPage()->getContent();
                $errorMsg .= "\nFull page content:\n" . $pageContent;
            }
            
            throw new Exception($errorMsg);
        }
    }
}