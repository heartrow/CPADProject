CREATE DATABASE IF NOT EXISTS railway
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; 

USE railway;

DROP TABLE IF EXISTS eco_tips; 
CREATE TABLE eco_tips (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    tip_text    TEXT NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO eco_tips (tip_text) VALUES 
('Unplug electronics when not in use. Standby power can account for up to 10% of your total household electricity bill!'),
('Wash clothes in cold water. About 75% to 90% of all the energy your washing machine uses goes solely into heating the water.'),
('Skip the heated dry cycle on your dishwasher. Letting your dishes air-dry can reduce the appliance''s energy use by up to 15%.'),
('Swap out your home''s remaining incandescent bulbs for LEDs. They use up to 75% less energy and last 25 times longer.'),
('Keep your refrigerator between 35°F and 38°F, and your freezer at 0°F. Keeping them any colder wastes energy unnecessarily.'),
('Clean your dryer’s lint trap before every load. A clogged screen forces the machine to run longer, burning up to 30% more energy.'),
('Lower your thermostat by 7°–10°F for 8 hours a day (like when you are asleep) to save up to 10% a year on heating costs.'),
('Cut your shower time down to 5 minutes. This small change can save up to 1,000 gallons of water per person every month.'),
('Repair leaky faucets promptly. A single faucet dripping at a rate of one drop per second can waste over 3,000 gallons of water a year.'),
('Turn off the tap while brushing your teeth. You can save up to 4 gallons of clean water every single time you brush.'),
('Only run your dishwasher and washing machine when they are completely full. This saves up to 300 to 800 gallons of water per month.'),
('Install low-flow faucet aerators. They cost just a few dollars but cut bathroom sink water consumption by up to 30%.'),
('Skip meat just one day a week. It takes roughly 1,800 gallons of water to produce a pound of beef compared to only 244 gallons for tofu.'),
('Plan your meals before grocery shopping. Around 30% to 40% of the entire food supply in developed countries ends up in landfills.'),
('Swap paper towels for washable cotton cloths. Creating paper towels consumes millions of trees and billions of gallons of water annually.'),
('Keep a reusable shopping bag in your car or backpack. A single plastic bag can take up to 500 years to degrade in a landfill.'),
('Freeze or repurpose leftover meals. Food waste rotting in landfills accounts for roughly 8% of all global greenhouse gas emissions.'),
('Compost your fruit peels and vegetable scraps. Composting prevents harmful methane production and creates nutrient-rich soil.'),
('Keep your car''s tires inflated to the recommended pressure. Under-inflated tires drop gas mileage by about 0.2% for every 1 psi drop.'),
('Clear heavy, unnecessary clutter out of your car trunk. An extra 100 pounds in your vehicle can reduce fuel economy by up to 1%.'),
('Plan and combine multiple short errands into one single trip. Cold engine starts can use twice as much fuel as a warm, continuous drive.'),
('Remove empty roof racks or cargo boxes when not in use. They create aerodynamic drag that can lower fuel efficiency by up to 20%.'),
('Avoid aggressive acceleration and hard braking. Safe, smooth driving can improve your highway gas mileage by 15% to 30%.'),
('Delete old emails and unsubscribe from unwanted newsletters. Storing useless data in cloud server farms consumes continuous cooling energy.'),
('Switch your phone, computer, and dashboard interfaces to Dark Mode. On OLED screens, this reduces battery power usage by up to 30%.'),
('Think twice before printing a document. The pulp and paper industry is one of the largest industrial energy consumers worldwide.'),
('Plug your home office setups into a smart power strip. It automatically cuts power to accessories when your computer goes to sleep.'),
('Purchase locally grown produce when possible. This eliminates the massive ''food miles'' and carbon emissions required to transport items.'),
('Switch from bottled body wash to traditional bar soap. Bar soaps require less energy to manufacture and eliminate plastic waste entirely.'),
('Choose durable, high-quality items over fast-fashion. Extending a garment''s life by just 9 months reduces its carbon footprint by 20%.'),
('Use a fiber-catching laundry bag or filter when washing synthetics. This prevents thousands of microplastics from washing into waterways.'),
('Install a programmable or smart thermostat. Setting it to optimize temperatures based on your schedule saves an average of 10-12% on heating.'),
('Repurpose glass jars from sauces and preserves to store bulk pantry dry goods instead of buying new plastic storage containers.'),
('When boiling water, only fill the kettle with the exact amount you need. Heating unneeded water accounts for massive daily energy waste.'),
('Choose loose-leaf tea with a reusable metal infuser. Many commercial paper tea bags contain hidden plastics that won''t biodegrade.'),
('Collect leftover water from washing vegetables or boiling pasta (once cooled) and use it to water your indoor house plants.'),
('Switch your default web search engine to a green alternative like Ecosia, which uses its advertising revenue to plant trees globally.'),
('Opt for digital receipts instead of paper thermal prints at checkout. Thermal receipts are coated with chemicals that prevent recycling.'),
('Use public transit, carpool, or bike to commute just once a week. This single shift reduces your personal transport emissions by 20%.'),
('Wash your synthetic activewear less frequently. Airing them out or spot-cleaning extends fabric lifecycle and curbs graywater microfibers.'),
('Plant native wildflowers or flora in your yard or balcony boxes. Native plants require significantly less water and aid local pollinators.'),
('Borrow tools, camping gear, or specialty kitchen appliances from friends or local resource libraries instead of buying items you only use once.'),
('Repair shoes, patch denim, or replace broken buttons rather than discarding garments. Extending clothing lifespans keeps textiles out of landfills.'),
('Rely on natural ventilation and cross-breezes during cool mornings or evenings before turning on power-heavy air conditioning units.'),
('Switch your home electricity plan to a verified 100% renewable energy or green tariff option if offered by your regional utility grid.');