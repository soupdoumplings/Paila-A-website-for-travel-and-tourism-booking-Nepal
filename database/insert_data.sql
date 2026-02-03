USE nepal_tours;

-- Insert default roles
INSERT INTO roles (id, name, description) VALUES 
(1, 'super_admin', 'Has full access to all settings and can manage other admins'),
(2, 'admin', 'Can manage tours and bookings'),
(3, 'user', 'Regular customer account'),
(4, 'tour_guide', 'Assigned to tours, no admin access')
ON DUPLICATE KEY UPDATE name=name;

-- Insert super admin
INSERT INTO users (username, email, password, role_id) 
SELECT 'ujShresthadmin', '2461787@paila.admin', '$2y$10$XJJRQsALbyI7RXBLPW07ZeNjRClJcMt.o/I/ygLvfAAQO0vNdOnW2', 1
WHERE NOT EXISTS (SELECT * FROM users WHERE username = 'ujShresthadmin');

-- Clear tour data
DELETE FROM tours;

-- Insert spring tours
INSERT INTO tours (title, location, price, duration, description, category, difficulty, max_group, highlights, image, best_season, altitude_max, permit_requirements, itinerary, inclusions, exclusions) VALUES
('Everest Base Camp Trek', 'Everest', 165000.00, '14 Days', 'Experience the world''s most iconic trek to the base of Mt. Everest. Journey through Sherpa villages, ancient monasteries, and breathtaking alpine landscapes with stunning views of the world''s highest peaks.', 'trekking', 'Hard', 12, 
'Scenic flight to Lukla\nNamche Bazaar market\nTengboche Monastery\nKala Patthar sunrise viewpoint\nEverest Base Camp at 5364m\nSherpa culture immersion',
'ebc.jpg', 'Spring, Autumn', 5545, 'TIMS Card, Sagarmatha National Park Entry Permit',
'Arrival in Kathmandu, hotel transfer and briefing\nFly to Lukla (2840m), trek to Phakding (2610m)\nTrek to Namche Bazaar (3440m)\nAcclimatization day in Namche, optional hike to Everest View Hotel\nTrek to Tengboche (3860m)\nTrek to Dingboche (4410m)\nAcclimatization day in Dingboche\nTrek to Lobuche (4910m)\nTrek to Gorak Shep (5140m), hike to EBC (5364m)\nHike to Kala Patthar (5545m) for sunrise, descend to Pheriche\nTrek back to Namche Bazaar\nTrek to Lukla\nFly back to Kathmandu\nDeparture day',
'Airport transfers\nDomestic flights (Kathmandu-Lukla-Kathmandu)\nTeahouse accommodation during trek\nExperienced English-speaking guide\nPorter service (1 porter for 2 trekkers)\nAll permit fees (TIMS, National Park)\nFirst aid kit\nGovernment taxes',
'International flights\nNepal visa fees\nLunch and dinner in Kathmandu\nPersonal trekking equipment\nTravel insurance\nDrinks and beverages\nTips for guide and porter\nPersonal expenses'),

('Annapurna Base Camp Trek', 'Annapurna', 95000.00, '10 Days', 'Trek to the heart of the Annapurna Sanctuary, surrounded by towering peaks. Experience diverse landscapes from lush rhododendron forests to high alpine meadows with spectacular mountain views.', 'trekking', 'Moderate', 14,
'Annapurna Base Camp at 4130m\nMachhapuchhre Base Camp\nNatural hot springs at Jhinu Danda\nGurung and Magar villages\nDiverse ecosystems\nClose-up mountain views',
'annapurna.jpg', 'Spring, Autumn', 4130, 'TIMS Card, ACAP Entry Permit',
'Drive from Pokhara to Nayapul, trek to Tikhedhunga\nTrek to Ghorepani (2850m)\nSunrise at Poon Hill (3210m), trek to Tadapani\nTrek to Chhomrong (2170m)\nTrek to Bamboo (2310m)\nTrek to Deurali (3230m)\nTrek to Annapurna Base Camp (4130m) via MBC\nDescend to Bamboo\nTrek to Jhinu Danda, relax in hot springs\nTrek to Nayapul, drive back to Pokhara',
'Pokhara-Nayapul-Pokhara transportation\nTeahouse accommodation\nExperienced trekking guide\nPorter service\nACАP and TIMS permits\nFirst aid kit\nAll government taxes',
'Kathmandu-Pokhara transportation\nMeals in Pokhara\nPersonal trekking gear\nTravel insurance\nBeverages\nTips\nPersonal expenses'),

('Langtang Valley Trek', 'Langtang', 75000.00, '9 Days', 'Discover the stunning Langtang Valley, known as the "Valley of Glaciers". Trek through beautiful Tamang villages, lush forests, and alongside the Langtang River with views of Langtang Lirung.', 'trekking', 'Moderate', 12,
'Kyanjin Gompa monastery (3870m)\nTserko Ri viewpoint (4984m)\nLangtang Glacier\nTamang heritage and culture\nCheese factories\nPanoramic mountain views',
'kathmandu_heritage.jpg', 'Spring, Autumn', 4984, 'TIMS Card, Langtang National Park Entry Permit',
'Drive from Kathmandu to Syabrubesi (1460m)\nTrek to Lama Hotel (2380m)\nTrek to Langtang Village (3430m)\nTrek to Kyanjin Gompa (3870m)\nAcclimatization day, optional hike to Tserko Ri (4984m)\nDescend to Lama Hotel\nTrek to Syabrubesi\nDrive back to Kathmandu\nReserve day for contingency',
'Kathmandu-Syabrubesi-Kathmandu transport\nTeahouse accommodation\nEnglish-speaking guide\nPorter service\nNational Park and TIMS fees\nFirst aid supplies\nGovernment taxes',
'Meals in Kathmandu\nPersonal equipment\nTravel insurance\nDrinks\nTips for staff\nEmergency evacuation\nPersonal expenses'),

('Manaslu Circuit Trek', 'Manaslu', 185000.00, '16 Days', 'Trek around the eighth highest mountain in the world. Experience remote villages, ancient monasteries, and cross the challenging Larkya La Pass with stunning views of Manaslu, Himlung Himal, and more.', 'trekking', 'Hard', 10,
'Larkya La Pass (5160m)\nManaslu Conservation Area\nRemote Tibetan Buddhist villages\nPungen Glacier\nBirendra Lake\nLess crowded alternative to Annapurna',
'annapurna.jpg', 'Spring, Autumn', 5160, 'Restricted Area Permit, ACAP, MCAP, TIMS',
'Drive Kathmandu to Soti Khola (700m)\nTrek to Machhakhola (930m)\nTrek to Jagat (1410m)\nTrek to Deng (1860m)\nTrek to Namrung (2660m)\nTrek to Lho (3180m)\nTrek to Samagaun (3530m)\nAcclimatization day in Samagaun\nTrek to Samdo (3860m)\nTrek to Dharamsala/Larkya Phedi (4460m)\nCross Larkya La Pass (5160m), descend to Bimthang (3720m)\nTrek to Tilije (2300m)\nTrek to Dharapani, drive to Besisahar\nDrive to Kathmandu\nBuffer day',
'All transportation (Kathmandu-trek-Kathmandu)\nBasic lodge accommodation\nExperienced guide and porter\nAll permits (RAP, MCAP, ACAP, TIMS)\nThree meals a day during trek\nFirst aid kit\nGovernment taxes and fees',
'Kathmandu hotel\nInternational flights\nTravel and rescue insurance\nExtra nights due to delays\nDrinks and snacks\nPersonal gear\nTips\nPersonal expenses');

-- Insert year-round tours
INSERT INTO tours (title, location, price, duration, description, category, difficulty, max_group, highlights, image, best_season, altitude_max, permit_requirements, itinerary, inclusions, exclusions) VALUES
('Kathmandu Valley Cultural Tour', 'Kathmandu', 25000.00, '4 Days', 'Explore the rich cultural heritage of Kathmandu Valley. Visit UNESCO World Heritage Sites including ancient temples, palaces, and stupas that showcase Nepal''s artistic and architectural brilliance.', 'culture', 'Beginner', 20,
'Swayambhunath Stupa (Monkey Temple)\nPashupatinath Temple\nBoudhanath Stupa\nKathmandu Durbar Square\nPatan Durbar Square\nBhaktapur Durbar Square\nTraditional Newari architecture',
'kathmandu_heritage.jpg', 'Year-round', 1400, 'Monument Entry Fees',
'Arrival, hotel check-in, evening orientation\nFull day Kathmandu sightseeing (Swayambhunath, Kathmandu Durbar Square, Patan)\nFull day Bhaktapur and Pashupatinath tour\nBoudhanath visit, departure preparation',
'Airport transfers\n3-star hotel accommodation with breakfast\nPrivate vehicle with driver\nEnglish-speaking guide\nAll monument entry fees\nGovernment taxes',
'Lunch and dinner\nInternational flights\nVisa fees\nTravel insurance\nPersonal expenses\nDrinks\nTips for guide and driver'),

('Chitwan Jungle Safari', 'Chitwan', 42000.00, '3 Days', 'Immerse yourself in the wilderness of Chitwan National Park. Spot endangered one-horned rhinos, Bengal tigers, and over 500 species of birds in this UNESCO World Heritage Site.', 'adventure', 'Beginner', 16,
'Jeep safari through jungle\nCanoe ride on Rapti River\nElephant breeding center visit\nJungle walk with naturalist\nTharu cultural dance\nBird watching\nWildlife spotting (rhino, deer, crocodiles)',
'chitwan.jpg', 'Year-round', 150, 'Chitwan National Park Entry Permit',
'Drive/fly from Kathmandu to Chitwan, evening Tharu cultural program\nFull day jungle activities (canoe ride, jungle walk, jeep safari)\nMorning bird watching, departure to Kathmandu',
'Kathmandu-Chitwan-Kathmandu transfers\nFull board accommodation in jungle resort\nAll jungle activities with guide\nNational Park fees\nTharu cultural show\nNaturalist guide',
'Meals in Kathmandu\nPersonal expenses\nDrinks and beverages\nTips for guides\nTravel insurance'),

('Pokhara Adventure Package', 'Pokhara', 38000.00, '5 Days', 'Experience the adventure capital of Nepal. Enjoy paragliding, boating on Phewa Lake, visiting caves, waterfalls, and stunning mountain views of the Annapurna range.', 'adventure', 'Beginner', 15,
'Paragliding with mountain views\nBoating on Phewa Lake\nSarangkot sunrise viewpoint\nDavis Falls and Gupteshwor Cave\nInternational Mountain Museum\nWorld Peace Pagoda\nLakeside strolls',
'kathmandu_heritage.jpg', 'Year-round', 1600, 'None',
'Drive/fly Kathmandu to Pokhara, lakeside exploration\nEarly morning Sarangkot sunrise, paragliding experience\nFull day sightseeing (Davis Falls, caves, museum, Peace Pagoda)\nLeisure day for optional activities (zip-line, bungee, ultra-light flight)\nReturn to Kathmandu',
'Kathmandu-Pokhara-Kathmandu transport\nHotel accommodation with breakfast\nParagliding with photos and video\nSightseeing with private vehicle\nEntry fees for monuments\nExperienced guide',
'Lunch and dinner\nOptional activities (ultra-light, bungee, zip-line)\nDrinks\nTravel insurance\nTips\nPersonal expenses');

-- Insert monsoon tours
INSERT INTO tours (title, location, price, duration, description, category, difficulty, max_group, highlights, image, best_season, altitude_max, permit_requirements, itinerary, inclusions, exclusions) VALUES
('Upper Mustang Trek', 'Mustang', 195000.00, '12 Days', 'Journey to the forbidden kingdom of Upper Mustang, a remote Tibetan Buddhist region with ancient walled cities, mysterious caves, and barren landscapes. Perfect for monsoon trekking in the rain shadow area.', 'trekking', 'Moderate', 10,
'Lo Manthang walled city\nAncient monasteries and caves\nTibetan Buddhist culture\nBarren Himalayan desert landscapes\nCho ser cave monastery\nRain shadow trek (good in monsoon)',
'annapurna.jpg', 'Monsoon, Autumn', 3840, 'Restricted Area Permit, ACAP, TIMS',
'Fly Kathmandu to Pokhara\nDrive Pokhara to Jomsom, trek to Kagbeni\nTrek to Chele (3050m)\nTrek to Syanbochen (3800m)\nTrek to Ghami (3520m)\nTrek to Tsarang (3560m)\nTrek to Lo Manthang (3840m)\nExploration day in Lo Manthang\nTrek to Drakmar (3810m)\nTrek to Ghiling (3806m)\nTrek to Chhusang (2980m)\nTrek to Jomsom, fly to Pokhara\nFly to Kathmandu',
'Domestic flights (Kathmandu-Pokhara, Jomsom-Pokhara-Kathmandu)\nJeep transfer Pokhara-Jomsom\nBasic lodge accommodation\nThree meals daily during trek\nExperienced guide and porter\nAll permits (RAP, ACAP, TIMS)\nFirst aid\nGovernment taxes',
'Kathmandu and Pokhara hotels\nMeals in cities\nInternational flights\nTravel insurance\nDrinks and snacks\nPersonal gear\nTips\nPersonal expenses\nEmergency evacuation'),

('Tilicho Lake Trek', 'Annapurna', 115000.00, '11 Days', 'Trek to one of the highest lakes in the world at 4919m. Combine stunning turquoise lake views with the classic Annapurna Circuit experience through diverse landscapes and cultures.', 'trekking', 'Hard', 12,
'Tilicho Lake (4919m) - one of world''s highest lakes\nThorong La Pass optional (5416m)\nDiverse landscapes\nManang Valley\nTilіcho Base Camp\nGangapurna glacier',
'annapurna.jpg', 'Monsoon, Autumn', 4919, 'TIMS, ACAP',
'Drive Kathmandu to Besisahar, to Chame (2710m)\nTrek to Pisang (3200m)\nTrek to Manang (3540m)\nAcclimatization in Manang\nTrek to Tilicho Base Camp (4150m)\nTrek to Tilicho Lake (4919m) and back to base camp\nTrek to Yak Kharka (4018m)\nTrek to Thorong Phedi (4450m)\nCross Thorong La (5416m), descend to Muktinath (3800m)\nDrive to Jomsom, fly to Pokhara\nDrive/fly to Kathmandu',
'Kathmandu-Besisahar-Kathmandu transport\nJomsom-Pokhara flight\nLodge accommodation\nAll meals during trek\nGuide and porter\nACWAP and TIMS permits\nOxygen meter and first aid\nGovernment taxes',
'Kathmandu/Pokhara hotels\nLunch/dinner in cities\nInternational flights\nTravel insurance\nPersonal equipment\nDrinks\nTips\nPersonal expenses'),

('Rara Lake Trek', 'Rara Lake', 145000.00, '10 Days', 'Discover Nepal''s largest and deepest lake in the remote far-western region. Trek through pristine forests, encounter rare wildlife, and experience the tranquility of this hidden gem.', 'trekking', 'Moderate', 12,
'Rara Lake (2990m) - largest lake in Nepal\nRara National Park\nPristine alpine scenery\nJuniper and pine forests\nRare flora and fauna\nRemote Malla and Thakuri villages\nFew tourists - off the beaten path',
'kathmandu_heritage.jpg', 'Monsoon, Spring', 3710, 'Rara National Park Entry, TIMS',
'Fly Kathmandu to Nepalgunj\nFly to Talcha (Mugu) airstrip, trek to Rara Lake (2 hrs)\nFull day exploring Rara Lake and surroundings\nTrek to Chhapre (2800m)\nTrek to Jumla (2540m)\nBuffer day for flight delays\nFly Jumla to Nepalgunj\nFly to Kathmandu\nReserve days (2 days)',
'All domestic flights (Kathmandu-Nepalgunj-Talcha/Jumla-Kathmandu)\nLodge/camping accommodation\nAll meals during trek\nCamping equipment if needed\nExperienced guide and porter\nNational Park and TIMS fees\nFirst aid kit',
'Kathmandu hotel\nMeals in cities\nInternational flights\nTravel insurance\nExtra costs due to flight delays\nDrinks\nPersonal gear\nTips\nPersonal expenses');


-- Insert sample bookings
INSERT INTO bookings (tour_id, customer_name, contact_email, travel_date, travelers, status) VALUES
(1, 'John Doe', 'john@example.com', '2026-04-15', 2, 'confirmed'),
(2, 'Jane Smith', 'jane@example.com', '2026-05-10', 4, 'pending'),
(3, 'Alex Johnson', 'alex@example.com', '2026-10-20', 1, 'confirmed')
ON DUPLICATE KEY UPDATE customer_name=customer_name;
