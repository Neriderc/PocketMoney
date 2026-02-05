# PocketMoney
PocketMoney is a self-hosted website for managing your kids (or your own) pocket money. You can create children, add accounts to each child (E.g. Spending, Saving, and Charity) and have money automatically allocated to their accounts. Responsive to mobile and desktop resolutions.

![screen shots showing the application. The first with two children listed as Fred and Mary. Fred has one account "Spending" with $-11.01 in it, Mary has three accounts. Spending has $12 in it and Savings and Charity have $0. The second screen shot shows a "Spending" account, with a balance of $-11.01, created and last updated dates, a "Back to child" button, an "Edit account" button an "Add transation" button, and a list of transactions currently showing one transaction of $-11.01 to buy a pony](screenshot.png)

## Install

PocketMoney is provided via a docker image. It is presumed you run your own reverse proxy providing SSL and other protections, or access only on your local network.

- Download the [compose.yaml](compose.yaml) 
- Update the default username and password - you can remove this line after the first startup
- Set the ports to [your port]:80, where [your port] is whatever port you wish to access PocketMoney on, leave the second number as 80
- Set your base domain (or [server ip[]:[port] / localhost:[port] if accessing locally)
- Start the container with `docker compose up -d`
- Go to your domain to access PocketMoney

If you're having trouble, feel free to [create an issue](https://github.com/Neriderc/PocketMoney/issues/new) and I will see if I can help.

## First steps

Now you've got it installed, log in with the credentials set in the `compose.yaml` file. You can remove them from the file now, store them in your password manager instead.

#### Add a household

You'll log in to a pretty empty screen. First, add a Household by clicking the "Settings" button at the top right then click "Add household". Give your household a name and description - if you will only have one household then you won't often see this, so don't overthink it.

#### Add a child

After adding your household, you'll get the opportunity to add children. Click "Add child" and fill in the name and date of birth. Date of birth is not mandatory, but can be used to calculate pocket money based on age.

#### Add accounts
After creating your first child, click the "Edit child" button, then "Add account". A child can have multiple accounts, such as "Spending", "Saving", "Charity" which you may have (real life) rules for when and where each can be spent.

Choose an icon. I like to use "Currency Dollar" for the Spending account, "Piggy Bank" for Savings, and "Gift" for Charity.

You can also choose a colour if you wish.

#### Set up a scheduled payment

Add any accounts you want by editing the child, then go back to the "Edit Child" button to add a "Scheduled Transaction". You can set up a fixed amount to go into the child's account each day, week, or month, or you can get more complicated. Here's how I do mine:

Add a description, "Pocket money". This will show on the transaction list each time it's added.

Set the amount - I use 1.

Set the amount calculation to "Based on child's age". The amount will now be multiplied by their age. For example, a 7 year old will now get $7 if you set the amount to 1.

Next, you can split between accounts. I choose the child's 3 accounts, and then set it to repeat weekly. Now a 9 year old will get $3 spending, $3 saving, and $3 charity money each week.

You can choose the next transaction date to pick the day you want it to go in. For example, if payments are weekly, pick the date of the next Saturday to have it paid on Saturday each week. If you pick a date in the past it will catch up on deposits for the missed dates.

With your transaction schedule set up, you have done the main part. Add any other children, and set up their accounts and scheduled transactions.

#### Purchases

When your child wants to puchase something, record this by adding a transaction. You can click the "Add Transaction" button that appears in multiple places where accounts are found, or from the home screen you can click the green + next to an account on a child's tile.

Add a description, this will be listed in the transactions list. Add the amount that they have paid. Choose "Purchase" as the transaction type. You can use "Deposit" if you need to give your child extra cash, say as payment for doing an ad hoc chore.

Add the transaction date, and a comment if you (or your child) might need a reminder of what exactly they spent money on.

Create the transaction, which will then show in the transaction list.

You'll notice that the account balance has updated.

#### Other

There are many other options to explore. Under a child you can add items to a wishlist, then set one of these items as a current savings goal. We have a minimum savings period which is why there is a "Can't buy before" date. This is just for information, it doesn't affect anything.

You are able to add multiple households if needed. Switch between them using a dropdown box that appears in the navigation bar when you have access to multiple households. A default household can be set in the settings.

You can also add new users, and grant them access to one or more households. For example, you might have a friend who also wants to use the application but doesn't self host. You can create a household for them, and grant them User permission just for that household. They can do all the actions for the household but can't see your household and can't add or edit households so long as you don't give them the Admin permission.

## Contribute

You can contribute in a number of ways.
- Code contributions are welcome
- If you can't code, feel free to open an issue to request features or report bugs
- Currently there are no translations to other languages. Maybe you'd like it if there were? Open an issue and let me know if you're willing to translate.

## Dev environment

These instructions may may assumptions around your environment, so please let me know if there were other steps you needed to do or if you are unable to set up your dev environment.

### Clone the repository
`git clone https://github.com/Neriderc/PocketMoney.git`

### Install dependencies

Install any extra dependencies as needed. E.g. composer or npm, PHP, etc

`cd PocketMoney/backend`  
`composer install`  

`cd ../frontend`  
`npm install`

And build the frontend:  
`npm run build`

## Compile the container
Ensure you are in the root of the repository then run:
`docker build --build-arg APP_ENV=dev -t pocketmoney:dev .`

The APP_ENV part is important as without it you won't get the dev tools needed to run in dev mode with debug enabled in the next step.

### Start container

Now create the compose.yaml file:
```
services:
  pocketmoney:
    image: pocketmoney:dev
    ports:
      - "8000:80"
    volumes:
      - ./data/db:/var/www/backend/var/db
    environment:
      APP_ENV: dev
      APP_DEBUG: 1
      DATABASE_URL: sqlite:///%kernel.project_dir%/var/db/data.db
      DEFAULT_USERNAME: admin
      DEFAULT_PASSWORD: changeme
```

Then access it at http://localhost:8000