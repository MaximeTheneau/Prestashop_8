// Import utils
import testContext from '@utils/testContext';

// Import FO pages
import {homePage} from '@pages/FO/classic/home';

import {expect} from 'chai';
import type {BrowserContext, Page} from 'playwright';
import {
  utilsPlaywright,
} from '@prestashop-core/ui-testing';

const baseContext: string = 'functional_FO_classic_homePage_checkSlider';

describe('FO - Home Page : Check slider', async () => {
  let browserContext: BrowserContext;
  let page: Page;

  // before and after functions
  before(async function () {
    browserContext = await utilsPlaywright.createBrowserContext(this.browser);
    page = await utilsPlaywright.newTab(browserContext);
  });

  after(async () => {
    await utilsPlaywright.closeBrowserContext(browserContext);
  });

  it('should open the shop page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'openShopFO', baseContext);

    await homePage.goTo(page, global.FO.URL);

    const result = await homePage.isHomePage(page);
    expect(result).to.eq(true);
  });

  it('should click in right arrow of the slider', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'clickOnRightSlideArrow', baseContext);

    let isVisible = await homePage.isSliderVisible(page, 1);
    expect(isVisible).to.eq(true);

    await homePage.clickOnLeftOrRightArrow(page, 'right');

    isVisible = await homePage.isSliderVisible(page, 2);
    expect(isVisible).to.eq(true);
  });

  it('should click in left arrow of the slider', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'clickOnLeftSlideArrow', baseContext);

    let isVisible = await homePage.isSliderVisible(page, 2);
    expect(isVisible).to.eq(true);

    await homePage.clickOnLeftOrRightArrow(page, 'left');

    isVisible = await homePage.isSliderVisible(page, 1);
    expect(isVisible).to.eq(true);
  });

  it('should check the slider URL', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'checkSliderURL', baseContext);

    const currentURL = await homePage.getSliderURL(page);
    expect(currentURL).to.contains('www.prestashop-project.org');
  });
});
