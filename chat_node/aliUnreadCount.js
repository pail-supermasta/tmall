//create global variables to be used in the beforeAll function
let browser
let page
const puppeteer = require('puppeteer');

require('dotenv').config();
const TelegramAPI = require('telegram-bot-api');
const telegram = new TelegramAPI({
    token: process.env.TELEGRAM_TOKEN,
});
//
// telegram.sendMessage({
//     chat_id: process.env.TELEGRAM_CHAT,
//     text: 'hello'
// });


const LOGINS = [{
        "login": "NezabudkaMR@yandex.ru",
        "pass": "EkutJ!Hau9.m3wf"
    }, {
        "login": "bestgoodsstore@yandex.ru",
        "pass": "cGbEsbS.V#WN23M"
    }, {
        "login": "novinkiooo@yandex.ru",
        "pass": "EkutJ!Hau9.m3wf"
    }, {
        "login": "NezabudkaiRobot@yandex.ru",
        "pass": "IR_+79169722555"
    }, {
        "login": "NezabudkaND@yandex.ru",
        "pass": "ND_+79169722555"
    }
];


let i = 0;

function func1(i, LOGINS, item) {
    console.log(item)
    const puppeteer = require('puppeteer');

    (async () => {
        console.log(item.login);

        // launch browser
        browser = await puppeteer.launch(
            {
                headless: true, // headless mode set to false so browser opens up with visual feedback
                slowMo: 25, // how slow actions should be
                args: ['--no-sandbox']
            }
        )

        // creates a new page in the opened browser

        page = await browser.newPage()
        await page.goto('https://msg.aliexpress.com/queryAllUnreadCount.htm?crmMsg=true&messageMsg=true&messMsg=true&orderMsg=true&chargebackMsg=true&disputeNoticeMsg=true&systemNoticeMsg=true&sellerGrowthMsg=true&spamMsg=true&bizMsg=false&newImMsg=true');
        await page.waitForSelector('#signInField');

        let signInField = await page.$x('//*[@id="signInField"]');

        if (signInField.length > 0) {
            //login
            console.log('please login');

            //log
            await page.click('#fm-login-id')
            await page.type('#fm-login-id', item.login)

            //pass
            await page.click('#fm-login-password')
            await page.type('#fm-login-password', item.pass)
            await page.waitForSelector('.fm-button.fm-submit.password-login')
            await page.click('.fm-button.fm-submit.password-login')


            await page.waitForSelector('pre')

            const pre = await page.$("pre");
            const MsgRaw = await (await pre.getProperty('textContent')).jsonValue();

            console.log('well done!' + MsgRaw);

            var msg = JSON.parse(MsgRaw);
            var newImMsg = msg.newImMsg;

            if(newImMsg>0){
                console.log(newImMsg)
                var msg = item.login + ': ' + newImMsg
                telegram.sendMessage({
                    chat_id: process.env.TELEGRAM_CHAT,
                    text: msg
                });
            }else if(newImMsg==0){
                // console.log('no messages')
                var msg = item.login + ' - ок сообщений нет'
                telegram.sendMessage({
                    chat_id: process.env.TELEGRAM_CHATERR,
                    text: msg
                });
            }



            browser.close();

            // send newImMsg to something

            // browser.close();


        } else {
            //get response
            console.log('grab');

            await page.waitForSelector('pre')

            const pre = await page.$("pre");
            const text = await (await pre.getProperty('textContent')).jsonValue();

            console.log('well done!' + text);


        }

        browser.on('disconnected', () => {
            if (i < LOGINS.length - 1 && item != undefined) {
                i++;
                func1(i, LOGINS, LOGINS[i]);
            } else {
                setTimeout(() => {
                    process.exit(0);
                }, 5000);
                return false;
            }
        });

    })();

}

func1(i, LOGINS, LOGINS[i]);




