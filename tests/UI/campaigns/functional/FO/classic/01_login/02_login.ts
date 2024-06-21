// Import utils
import testContext from '@utils/testContext';

// Import FO pages
import {homePage} from '@pages/FO/classic/home';
import {loginPage} from '@pages/FO/classic/login';

import {
  dataCustomers,
  FakerCustomer,
  utilsPlaywright,
} from '@prestashop-core/ui-testing';

import {expect} from 'chai';
import type {BrowserContext, Page} from 'playwright';

const baseContext: string = 'functional_FO_classic_login_login';

describe('FO - Login : Login in FO', async () => {
  let browserContext: BrowserContext;
  let page: Page;

  const firstCredentialsData: FakerCustomer = new FakerCustomer();
  const secondCredentialsData: FakerCustomer = new FakerCustomer({password: dataCustomers.johnDoe.password});
  const thirdCredentialsData: FakerCustomer = new FakerCustomer({email: dataCustomers.johnDoe.email});

  // before and after functions
  before(async function () {
    browserContext = await utilsPlaywright.createBrowserContext(this.browser);
    page = await utilsPlaywright.newTab(browserContext);
  });

  after(async () => {
    await utilsPlaywright.closeBrowserContext(browserContext);
  });

  it('should open the shop page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToShopFO', baseContext);

    await homePage.goTo(page, global.FO.URL);

    const result = await homePage.isHomePage(page);
    expect(result).to.eq(true);
  });

  it('should go to login page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToLoginPage', baseContext);

    await homePage.goToLoginPage(page);

    const pageTitle = await loginPage.getPageTitle(page);
    expect(pageTitle).to.equal(loginPage.pageTitle);
  });

  it('should enter an invalid credentials', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'enterInvalidCredentials', baseContext);

    await loginPage.customerLogin(page, firstCredentialsData, false);

    const loginError = await loginPage.getLoginError(page);
    expect(loginError).to.contains(loginPage.loginErrorText);
  });

  it('should enter an invalid email', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'enterInvalidEmail', baseContext);

    await loginPage.customerLogin(page, secondCredentialsData, false);

    const loginError = await loginPage.getLoginError(page);
    expect(loginError).to.contains(loginPage.loginErrorText);
  });

  it('should enter an invalid password', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'enterInvalidPassword', baseContext);

    await loginPage.customerLogin(page, thirdCredentialsData, false);

    const loginError = await loginPage.getLoginError(page);
    expect(loginError).to.contains(loginPage.loginErrorText);
  });

  it('should check password type', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'checkPasswordType', baseContext);

    const inputType = await loginPage.getPasswordType(page);
    expect(inputType).to.equal('password');
  });

  it('should click on show button and check the password', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'clickOnShowButton', baseContext);

    const inputType = await loginPage.showPassword(page);
    expect(inputType).to.equal('text');
  });

  it('should enter a valid credentials', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'enterValidCredentials', baseContext);

    await loginPage.customerLogin(page, dataCustomers.johnDoe);

    const isCustomerConnected = await loginPage.isCustomerConnected(page);
    expect(isCustomerConnected, 'Customer is not connected!').to.eq(true);

    const result = await homePage.isHomePage(page);
    expect(result).to.eq(true);
  });
});
