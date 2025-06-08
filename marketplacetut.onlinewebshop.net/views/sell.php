<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Sales Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/sell.css">
</head>

<header>
    <div class="container header-container">
        <a href="../index.php" class="logo">Easy<span>Trade</span></a>
        <div class="nav-links">
            <a href="../index.php">Home</a>
            <a href="seller_dashboard.php" class="nav-link">Dashboard</a>
        </div>
    </div>
</header>

<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto py-8 px-4">
        <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">Sell Your Items</h1>

        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row gap-6">
            <!-- Item Listing Form -->
            <div class="bg-white p-6 rounded-lg shadow-md flex-1">
                <h2 class="text-xl font-semibold mb-4">Create New Listing</h2>

                <form method="POST" action="../controllers/sellController.php" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-gray-700 mb-1">Item Image</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                                <div id="image-preview" class="mb-2 hidden">
                                    <img id="preview" src="#" alt="Preview" class="max-h-48 mx-auto">
                                </div>
                                <div id="no-image" class="text-gray-500 mb-2">No image selected</div>
                                <input type="file" name="image" accept="image/*" class="hidden" id="image-upload"
                                    onchange="previewImage()">
                                <label for="image-upload"
                                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md cursor-pointer">
                                    Upload Image
                                </label>
                                <button type="button" id="analyze-image" disabled
                                    class="ml-2 bg-gray-400 text-white py-2 px-4 rounded-md cursor-not-allowed">
                                    Analyze Image
                                </button>
                            </div>
                            <div id="image-analysis-results" class="mt-3 hidden">
                                <div class="bg-blue-50 p-3 rounded-md">
                                    <h3 class="font-medium text-blue-700">Image Analysis Results</h3>
                                    <ul id="predictions-list" class="mt-2 space-y-1 text-sm"></ul>
                                    <div class="mt-2 flex space-x-2">
                                        <button type="button" id="apply-category"
                                            class="text-xs bg-blue-600 hover:bg-blue-700 text-white py-1 px-2 rounded">
                                            Apply Category
                                        </button>
                                        <button type="button" id="generate-description"
                                            class="text-xs bg-green-600 hover:bg-green-700 text-white py-1 px-2 rounded">
                                            Generate Description
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1">Item Name*</label>
                            <input type="text" name="itemName" id="itemName" required
                                class="w-full border border-gray-300 rounded-md p-2">
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1">Category*</label>
                            <select name="category" id="category" required
                                class="w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Category</option>
                                <option value="Electronic">Electronic</option>
                                <option value="clothes">Clothes</option>
                                <option value="Home&Garden">Home & Garden</option>
                                <option value="Toys&Games">Toys & Games</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1">Condition*</label>
                            <select name="condition" required class="w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Condition</option>
                                <option value="new">New</option>
                                <option value="used">Used</option>
                                <option value="refurbished">Refurbished</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1">Purchase Date</label>
                            <input type="date" name="purchaseDate" class="w-full border border-gray-300 rounded-md p-2">
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1">Price (R)*</label>
                            <input type="number" name="price" required min="0.01" step="0.01"
                                class="w-full border border-gray-300 rounded-md p-2">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-gray-700 mb-1">Description*</label>
                            <textarea name="description" id="description" required rows="4"
                                class="w-full border border-gray-300 rounded-md p-2"></textarea>
                        </div>

                        <div class="col-span-2">
                            <button type="submit" name="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md">
                                List Item for Sale
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Active Listings -->
            <div class="bg-white p-6 rounded-lg shadow-md flex-1">
                <h2 class="text-xl font-semibold mb-4">Your Listings</h2>
                <div id="listing-container" class="space-y-4">
                    <div class="text-gray-500">Loading listings...</div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function loadListings() {
            fetch('../controllers/fetch_listings.php')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('listing-container').innerHTML = html;

                    // Attach event listeners for delete buttons
                    document.querySelectorAll('.delete-btn').forEach(button => {
                        button.addEventListener('click', function (e) {
                            e.preventDefault();
                            const id = this.dataset.id;
                            if (confirm('Are you sure you want to remove this listing?')) {
                                deleteListing(id);
                            }
                        });
                    });
                });
        }

        function deleteListing(id) {
            fetch('../controllers/delete_listing.php?id=' + id)
                .then(response => response.text())
                .then(result => {
                    loadListings(); // Reload listings after deletion
                });
        }

        // Load on page load
        document.addEventListener('DOMContentLoaded', loadListings);
        // Image preview functionality
        function previewImage() {
            const preview = document.getElementById('preview');
            const fileInput = document.getElementById('image-upload');
            const previewContainer = document.getElementById('image-preview');
            const noImageText = document.getElementById('no-image');
            const analyzeBtn = document.getElementById('analyze-image');

            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    preview.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                    noImageText.classList.add('hidden');
                    analyzeBtn.disabled = false;
                    analyzeBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                    analyzeBtn.classList.add('bg-purple-600', 'hover:bg-purple-700', 'cursor-pointer');
                }

                reader.readAsDataURL(fileInput.files[0]);
            }
        }

        /**
         * Categorizes an item based on image recognition predictions
         * @param {Array} predictions - Array of prediction objects from image recognition API
         * @returns {Object} - Object containing category, confidence, and item type suggestion
         */
        function categorizeItem(predictions) {
            if (!predictions || !predictions.length) {
                return { category: null, confidence: 0, itemType: null };
            }

            // Category mappings with relevant keywords
            const categoryMappings = {
                "Electronic": [
                    // Devices
                    "mobile_phone", "laptop", "computer", "desktop", "monitor", "television", "tv", "screen",
                    "keyboard", "mouse", "tablet", "ipad", "iphone", "smartphone", "phone", "cellphone",
                    "headphone", "earphone", "speaker", "microphone", "printer", "scanner", "camera", "webcam",
                    // Electronics
                    "remote", "controller", "router", "modem", "adapter", "charger", "battery", "power_bank",
                    "calculator", "watch", "smartwatch", "clock", "radio", "projector", "console", "playstation",
                    "xbox", "nintendo", "amplifier", "receiver", "dvd", "bluray", "harddisk", "ssd", "usb", "cpu",
                    "gpu", "motherboard", "processor", "ram", "memory_card"
                ],

                "clothes": [
                    // Clothing
                    "shirt", "t-shirt", "polo", "blouse", "sweater", "hoodie", "jacket", "coat", "blazer",
                    "suit", "dress", "gown", "skirt", "jeans", "pants", "trousers", "shorts", "leggings",
                    "sweatpants", "underwear", "socks", "tie", "scarf", "glove", "belt", "suspender",
                    // Footwear
                    "shoe", "sneaker", "boot", "sandal", "slipper", "heel", "footwear",
                    // Accessories
                    "hat", "cap", "beanie", "beret", "headband", "bandana", "wristband", "bracelet",
                    "necklace", "ring", "earring", "jewelry", "watch", "sunglasses", "glasses", "backpack",
                    "bag", "purse", "wallet", "handbag"
                ],

                "Home&Garden": [
                    // Furniture
                    "chair", "table", "desk", "sofa", "couch", "bed", "mattress", "cabinet", "drawer",
                    "shelf", "bookcase", "stool", "bench", "ottoman", "nightstand", "wardrobe", "closet",
                    // Kitchen
                    "refrigerator", "fridge", "oven", "stove", "microwave", "toaster", "blender", "mixer",
                    "kettle", "pot", "pan", "plate", "bowl", "cup", "mug", "glass", "cutlery", "knife",
                    "fork", "spoon", "utensil", "chopping_board", "cutting_board",
                    // Home decor
                    "lamp", "light", "chandelier", "mirror", "clock", "vase", "frame", "painting", "curtain",
                    "blind", "rug", "carpet", "pillow", "cushion", "blanket", "towel", "shower",
                    // Garden
                    "plant", "flower", "tree", "pot", "planter", "garden", "lawn", "grass", "soil",
                    "fertilizer", "seed", "hose", "sprinkler", "shovel", "rake", "lawnmower"
                ],

                "Toys&Games": [
                    // Toys
                    "toy", "doll", "action_figure", "stuffed_animal", "teddy", "puzzle", "block", "lego",
                    "car", "truck", "train", "plane", "robot", "ball", "bat", "racket", "kite", "frisbee",
                    // Games
                    "game", "board_game", "card_game", "chess", "dice", "controller", "console", "video_game",
                    "gamepad", "joystick", "puzzle", "rubik"
                ]
            };

            // Calculate score for each category based on predictions
            const scores = {};
            Object.keys(categoryMappings).forEach(category => {
                scores[category] = 0;
            });

            // Process each prediction
            predictions.forEach(prediction => {
                const predictionText = prediction.description.toLowerCase();
                const probability = prediction.probability;

                // Check each category for matching keywords
                Object.keys(categoryMappings).forEach(category => {
                    categoryMappings[category].forEach(keyword => {
                        if (predictionText.includes(keyword.toLowerCase()) ||
                            keyword.toLowerCase().includes(predictionText)) {
                            // Add the probability score to this category
                            scores[category] += probability;
                        }
                    });
                });
            });

            // Find the category with the highest score
            let bestCategory = null;
            let bestScore = 0;

            Object.keys(scores).forEach(category => {
                if (scores[category] > bestScore) {
                    bestCategory = category;
                    bestScore = scores[category];
                }
            });

            // If no strong match found, use the top prediction's category
            if (bestScore === 0) {
                const topPrediction = predictions[0].description.toLowerCase();

                // Simple fallback categorization
                if (/electronic|device|gadget|tech/i.test(topPrediction)) {
                    bestCategory = "Electronic";
                } else if (/cloth|wear|apparel|garment|shoe|fashion/i.test(topPrediction)) {
                    bestCategory = "clothes";
                } else if (/home|house|furniture|kitchen|garden|decor/i.test(topPrediction)) {
                    bestCategory = "Home&Garden";
                } else if (/toy|game|play/i.test(topPrediction)) {
                    bestCategory = "Toys&Games";
                } else {
                    // Default to the category that seems most plausible based on the top prediction
                    bestCategory = guessDefaultCategory(topPrediction);
                }

                // Assign a low confidence score
                bestScore = 0.3;
            }

            return {
                category: bestCategory,
                confidence: bestScore,
                itemType: predictions[0].description
            };
        }

        /**
         * Attempts to guess a default category when no strong matches are found
         * @param {string} item - The item description
         * @returns {string} - The best guess category
         */
        function guessDefaultCategory(item) {
            // Electronic devices are often complex manufactured items
            if (/device|digital|electric|machine|system|equipment/i.test(item)) {
                return "Electronic";
            }

            // Clothes often related to people or body parts
            if (/wear|person|human|body|fashion/i.test(item)) {
                return "clothes";
            }

            // Home items are often furniture or household objects
            if (/room|house|indoor|domestic|furniture/i.test(item)) {
                return "Home&Garden";
            }

            // Default to Home & Garden as it's the broadest category
            return "Home&Garden";
        }

        /**
         * Generates a description based on image recognition results
         * @param {Array} predictions - Array of prediction objects
         * @param {string} itemName - Optional name of the item
         * @returns {string} - Generated description
         */
        function generateItemDescription(predictions, itemName = "") {
            if (!predictions || !predictions.length) {
                return "No description available.";
            }

            // Use provided item name or default to top prediction
            const name = itemName || predictions[0].description;
            const formattedName = name.charAt(0).toUpperCase() + name.slice(1);

            // Get top 3 predictions for additional details
            const topPredictions = predictions.slice(0, 3);

            // Main description
            let description = `This is a ${formattedName}`;

            // Add condition placeholder
            description += " in good condition";

            // Add features from other predictions if they're different enough
            const additionalFeatures = topPredictions
                .slice(1)
                .filter(p => {
                    const mainDesc = predictions[0].description.toLowerCase();
                    const thisDesc = p.description.toLowerCase();
                    return !mainDesc.includes(thisDesc) && !thisDesc.includes(mainDesc);
                })
                .map(p => p.description);

            if (additionalFeatures.length > 0) {
                description += ` with features similar to ${additionalFeatures.join(" and ")}`;
            }

            // Add generic selling points based on category
            const { category } = categorizeItem(predictions);

            if (category === "Electronic") {
                description += ". This item is fully functional and has been well maintained. ";
                description += "All necessary components are included.";
            } else if (category === "clothes") {
                description += ". This item is clean and from a smoke-free home. ";
                description += "The material is in excellent shape with no stains or damage.";
            } else if (category === "Home&Garden") {
                description += ". This item would make a great addition to your home. ";
                description += "It's been kept in great condition and is ready for immediate use.";
            } else if (category === "Toys&Games") {
                description += ". This item provides hours of fun and entertainment. ";
                description += "It's complete with all the necessary pieces.";
            } else {
                description += ". This item is ready for a new home.";
            }

            // Generic closing
            description += " Please contact me with any questions about this item.";

            return description;
        }

        /**
         * Estimates the price of an item based on its attributes
         * @param {Object} itemDetails - Details about the item
         * @param {string} itemDetails.category - The category of the item
         * @param {string} itemDetails.condition - The condition of the item (new, used, refurbished)
         * @param {string} itemDetails.name - The name of the item
         * @param {string} itemDetails.description - The description of the item
         * @param {Date|string} [itemDetails.purchaseDate] - The purchase date of the item (optional)
         * @param {Array} [itemDetails.predictions] - Image recognition predictions if available
         * @returns {Object} - Object containing estimated price and reasoning
         */
        function estimateItemPrice(itemDetails) {
            // Base prices by category (in Rands)
            const basePrices = {
                "Electronic": {
                    low: 250,
                    medium: 1500,
                    high: 5000
                },
                "clothes": {
                    low: 100,
                    medium: 350,
                    high: 800
                },
                "Home&Garden": {
                    low: 150,
                    medium: 600,
                    high: 2000
                },
                "Toys&Games": {
                    low: 120,
                    medium: 400,
                    high: 1200
                }
            };

            // Condition multipliers
            const conditionMultipliers = {
                "new": 1.0,
                "used": 0.6,
                "refurbished": 0.8
            };

            // Age depreciation factors (yearly)
            const yearlyDepreciation = {
                "Electronic": 0.2,      // Electronics depreciate faster
                "clothes": 0.15,
                "Home&Garden": 0.1,
                "Toys&Games": 0.15
            };

            // Keywords that might indicate higher value
            const premiumKeywords = {
                "Electronic": ["apple", "iphone", "macbook", "samsung", "sony", "gaming", "professional", "wireless", "bluetooth", "smart", "digital", "4k", "uhd", "oled", "ssd", "notebook", "tablet", "high-end", "premium", "reflex_camera", "washer"],
                "clothes": ["leather", "designer", "brand", "wool", "silk", "vintage", "limited", "edition", "handmade", "formal", "suit", "dress", "cashmere", "custom", "running_shoe",],
                "Home&Garden": ["solid wood", "stainless", "antique", "vintage", "handcrafted", "luxury", "premium", "designer", "marble", "genuine", "authentic", "imported"],
                "Toys&Games": ["collector", "limited", "edition", "vintage", "rare", "complete", "set", "series", "electronic", "educational", "interactive"]
            };

            // Validate inputs
            if (!itemDetails.category || !itemDetails.condition || !itemDetails.name) {
                return {
                    estimatedPrice: 0,
                    confidence: "low",
                    reasoning: "Insufficient details provided to estimate price."
                };
            }

            // Get base price category (low, medium, high)
            let priceCategory = "medium"; // Default to medium
            let premiumScore = 0;

            // Analyze item name and description for keywords
            const textToAnalyze = (itemDetails.name + " " + (itemDetails.description || "")).toLowerCase();

            // Check for premium keywords
            if (premiumKeywords[itemDetails.category]) {
                premiumKeywords[itemDetails.category].forEach(keyword => {
                    if (textToAnalyze.includes(keyword.toLowerCase())) {
                        premiumScore += 0.5;
                    }
                });
            }

            // Determine price category based on premium score
            if (premiumScore >= 2) {
                priceCategory = "high";
            } else if (premiumScore >= 0.5) {
                priceCategory = "medium";
            } else {
                priceCategory = "low";
            }

            // Get base price for the category
            let estimatedPrice = basePrices[itemDetails.category][priceCategory];

            // Apply condition multiplier
            estimatedPrice *= conditionMultipliers[itemDetails.condition] || 0.7; // Default to 0.7 if condition not recognized

            // Apply age depreciation if purchase date is available
            if (itemDetails.purchaseDate) {
                const purchaseDate = new Date(itemDetails.purchaseDate);
                const currentDate = new Date();

                // Check if purchase date is valid
                if (!isNaN(purchaseDate.getTime())) {
                    const ageInYears = (currentDate - purchaseDate) / (1000 * 60 * 60 * 24 * 365);

                    if (ageInYears > 0) {
                        // Apply depreciation based on age, but don't depreciate below 30% of the adjusted price
                        const maxDepreciation = 0.7;
                        const depreciation = Math.min(
                            yearlyDepreciation[itemDetails.category] * ageInYears,
                            maxDepreciation
                        );

                        estimatedPrice *= (1 - depreciation);
                    }
                }
            }

            // Adjust based on image recognition predictions if available
            if (itemDetails.predictions && itemDetails.predictions.length > 0) {
                // Check confidence of top prediction
                const topPrediction = itemDetails.predictions[0];

                // If high confidence prediction for premium item, boost price
                if (topPrediction.probability > 0.85) {
                    const predictionText = topPrediction.description.toLowerCase();

                    // Check if prediction suggests premium item
                    let isPremium = false;
                    if (premiumKeywords[itemDetails.category]) {
                        for (const keyword of premiumKeywords[itemDetails.category]) {
                            if (predictionText.includes(keyword.toLowerCase())) {
                                isPremium = true;
                                break;
                            }
                        }
                    }

                    if (isPremium) {
                        estimatedPrice *= 1.2; // 20% boost for high-confidence premium items
                    }
                }
            }

            // Round to nearest 10 Rands
            estimatedPrice = Math.round(estimatedPrice / 10) * 10;

            // Generate reasoning
            let reasoning = `Based on ${itemDetails.category} category, ${itemDetails.condition} condition`;

            if (priceCategory === "high") {
                reasoning += ", premium features";
            } else if (priceCategory === "low") {
                reasoning += ", standard features";
            }

            if (itemDetails.purchaseDate) {
                const purchaseDate = new Date(itemDetails.purchaseDate);
                if (!isNaN(purchaseDate.getTime())) {
                    reasoning += `, age (${new Date().getFullYear() - purchaseDate.getFullYear()} years)`;
                }
            }

            // Determine confidence level
            let confidence = "medium";
            if ((itemDetails.description && itemDetails.description.length > 50) &&
                itemDetails.purchaseDate && premiumScore > 0) {
                confidence = "high";
            } else if (!itemDetails.description || premiumScore === 0) {
                confidence = "low";
            }

            // Return estimate with reasoning
            return {
                estimatedPrice: estimatedPrice,
                confidence: confidence,
                reasoning: reasoning + "."
            };
        }

        // Function to handle the UI integration
        function setupPriceEstimator() {
            // Add price estimate button after the price input
            const priceInput = document.querySelector('input[name="price"]');
            if (!priceInput) return;

            const priceContainer = priceInput.parentElement;

            // Create estimate button
            const estimateBtn = document.createElement('button');
            estimateBtn.type = 'button';
            estimateBtn.className = 'mt-2 bg-blue-500 hover:bg-blue-600 text-white text-sm py-1 px-3 rounded';
            estimateBtn.textContent = 'Estimate Price';

            // Create result container
            const estimateResult = document.createElement('div');
            estimateResult.id = 'price-estimate-result';
            estimateResult.className = 'mt-2 text-sm hidden';

            // Add elements to the DOM
            priceContainer.appendChild(estimateBtn);
            priceContainer.appendChild(estimateResult);

            // Add event listener
            estimateBtn.addEventListener('click', function () {
                // Gather item details
                const itemDetails = {
                    name: document.getElementById('itemName').value,
                    category: document.getElementById('category').value,
                    condition: document.querySelector('select[name="condition"]').value,
                    description: document.getElementById('description').value,
                    purchaseDate: document.querySelector('input[name="purchaseDate"]').value,
                    predictions: window.imagePredictions || []
                };

                // Check if we have minimum required data
                if (!itemDetails.name || !itemDetails.category || !itemDetails.condition) {
                    estimateResult.innerHTML = `
        <div class="text-yellow-700 bg-yellow-100 p-2 rounded">
          <strong>Missing information:</strong> Please fill in the item name, category, and condition first.
        </div>
      `;
                    estimateResult.classList.remove('hidden');
                    return;
                }

                // Get price estimate
                const estimate = estimateItemPrice(itemDetails);

                // Show result with different styling based on confidence
                let confidenceColor = 'yellow';
                if (estimate.confidence === 'high') {
                    confidenceColor = 'green';
                } else if (estimate.confidence === 'low') {
                    confidenceColor = 'red';
                }

                estimateResult.innerHTML = `
      <div class="bg-${confidenceColor}-100 border border-${confidenceColor}-200 text-${confidenceColor}-800 p-2 rounded">
        <div class="flex justify-between items-center">
          <strong>Estimated Price:</strong> 
          <span class="text-lg font-bold">R${estimate.estimatedPrice}</span>
        </div>
        <div class="text-xs mt-1">
          <div>Confidence: <span class="font-medium">${estimate.confidence}</span></div>
          <div>${estimate.reasoning}</div>
        </div>
        <button id="apply-estimate" class="mt-2 bg-${confidenceColor}-600 hover:bg-${confidenceColor}-700 text-white text-xs py-1 px-2 rounded">
          Apply this estimate
        </button>
      </div>
    `;

                estimateResult.classList.remove('hidden');

                // Add event listener to apply button
                document.getElementById('apply-estimate').addEventListener('click', function () {
                    priceInput.value = estimate.estimatedPrice;
                    estimateResult.classList.add('hidden');
                });
            });
        }

        // Initialize all functionality when the DOM is fully loaded
        document.addEventListener('DOMContentLoaded', function () {
            // Set up the file input for image preview
            const fileInput = document.getElementById('image-upload');
            if (fileInput) {
                fileInput.addEventListener('change', previewImage);
            }

            // Event listener for analyze image button
            const analyzeButton = document.getElementById('analyze-image');
            if (analyzeButton) {
                analyzeButton.addEventListener('click', function () {
                    const fileInput = document.getElementById('image-upload');
                    if (!fileInput.files || !fileInput.files[0]) return;

                    const formData = new FormData();
                    formData.append('image', fileInput.files[0]);

                    // Show loading state
                    this.textContent = 'Analyzing...';
                    this.disabled = true;

                    // Fixed URL - removed double slash
                    fetch('https://easytrade-production-3b3d.up.railway.app/recognize_image', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            // Display results
                            const resultsContainer = document.getElementById('image-analysis-results');
                            const predictionsList = document.getElementById('predictions-list');

                            if (predictionsList) {
                                predictionsList.innerHTML = '';
                                if (data.predictions) {
                                    data.predictions.forEach(pred => {
                                        const li = document.createElement('li');
                                        const percentage = (pred.probability * 100).toFixed(1);
                                        li.innerHTML = `<span class="font-medium">${pred.description}</span> - ${percentage}% probability`;
                                        predictionsList.appendChild(li);
                                    });

                                    // Store predictions for later use
                                    window.imagePredictions = data.predictions;

                                    // Show the results container
                                    if (resultsContainer) {
                                        resultsContainer.classList.remove('hidden');
                                    }
                                }
                            }

                            // Reset button
                            this.textContent = 'Analyze Image';
                            this.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error analyzing image:', error);
                            alert('Failed to analyze image. Please try again.');

                            // Reset button
                            this.textContent = 'Analyze Image';
                            this.disabled = false;
                        });
                });
            }

            // Event listener for apply category button
            const applyCategoryButton = document.getElementById('apply-category');
            if (applyCategoryButton) {
                applyCategoryButton.addEventListener('click', function () {
                    if (!window.imagePredictions || !window.imagePredictions.length) return;

                    const categoryResult = categorizeItem(window.imagePredictions);
                    const categorySelect = document.getElementById('category');
                    const itemNameInput = document.getElementById('itemName');

                    // Apply category if available
                    if (categoryResult.category && categorySelect) {
                        for (let i = 0; i < categorySelect.options.length; i++) {
                            if (categorySelect.options[i].value === categoryResult.category) {
                                categorySelect.selectedIndex = i;
                                break;
                            }
                        }
                    }

                    // Suggest item name if empty and input exists
                    if (itemNameInput && !itemNameInput.value && categoryResult.itemType) {
                        itemNameInput.value = categoryResult.itemType.charAt(0).toUpperCase() + categoryResult.itemType.slice(1);
                    }
                });
            }

            // Event listener for generate description button
            const generateDescriptionButton = document.getElementById('generate-description');
            if (generateDescriptionButton) {
                generateDescriptionButton.addEventListener('click', function () {
                    if (!window.imagePredictions || !window.imagePredictions.length) return;

                    const descriptionArea = document.getElementById('description');
                    if (!descriptionArea) return;

                    const itemNameInput = document.getElementById('itemName');
                    const itemName = itemNameInput ? itemNameInput.value : '';

                    // Generate description
                    const generatedDescription = generateItemDescription(window.imagePredictions, itemName);

                    // Only set if the current description is empty
                    if (!descriptionArea.value.trim()) {
                        descriptionArea.value = generatedDescription;
                    } else if (confirm("Replace current description with generated one?")) {
                        descriptionArea.value = generatedDescription;
                    }
                });
            }

            // Setup price estimator
            // setupPriceEstimator();
        });
        //////#








        ////
        // Function to get market price data from Takealot API
        async function getMarketPrices(searchTerm) {
            try {
                const response = await fetch(`https://render-easytrade-production.up.railway.app/api/prices?search=${encodeURIComponent(searchTerm)}`);

                if (!response.ok) {
                    throw new Error(`API returned status: ${response.status}`);
                }

                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Error fetching market prices:', error);
                return null;
            }
        }


        /**
         * Enhanced price estimator that incorporates market data
         * @param {Object} itemDetails - Base item details
         * @param {Object} marketData - Data from Takealot API (optional)
         * @returns {Object} - Price estimate with market comparison
         */
        function enhancedPriceEstimator(itemDetails, marketData = null) {
            // Get base estimate from existing function
            const baseEstimate = estimateItemPrice(itemDetails);

            // If no market data, return base estimate
            if (!marketData || !marketData.results || !marketData.results.prices || marketData.results.prices.length === 0) {
                return {
                    ...baseEstimate,
                    marketComparison: null
                };
            }

            // Extract market statistics
            const { average, median, min, max } = marketData.results.stats;

            // Calculate competitive price based on condition
            let competitivePrice;
            let marketPosition;

            switch (itemDetails.condition) {
                case 'new':
                    // For new items, price near but slightly below average market price
                    competitivePrice = average * 0.9;
                    marketPosition = 'slightly below average market price';
                    break;
                case 'used':
                    // For used items, price between minimum and median, closer to minimum
                    competitivePrice = min + (median - min) * 0.3;
                    marketPosition = 'in the lower 30% of market range';
                    break;
                case 'refurbished':
                    // For refurbished, price between median and average
                    competitivePrice = (median + average) / 2;
                    marketPosition = 'in mid-range for similar items';
                    break;
                default:
                    competitivePrice = median;
                    marketPosition = 'at median market price';
            }

            // Blend base estimate with market data
            // Weight based on confidence of base estimate
            let confidenceWeight = 0.5; // Default medium confidence
            if (baseEstimate.confidence === 'high') {
                confidenceWeight = 0.7;
            } else if (baseEstimate.confidence === 'low') {
                confidenceWeight = 0.3;
            }

            // Blend the prices
            const blendedPrice = Math.round((baseEstimate.estimatedPrice * confidenceWeight +
                competitivePrice * (1 - confidenceWeight)) / 10) * 10;

            return {
                estimatedPrice: blendedPrice,
                confidence: baseEstimate.confidence,
                reasoning: baseEstimate.reasoning,
                marketComparison: {
                    average,
                    median,
                    min,
                    max,
                    sampleSize: marketData.results.count,
                    marketPosition
                }
            };
        }

        // Update the setupPriceEstimator function to incorporate market data
        function setupPriceEstimator() {
            // Add price estimate button after the price input
            const priceInput = document.querySelector('input[name="price"]');
            if (!priceInput) return;

            const priceContainer = priceInput.parentElement;

            // Create estimate button
            const estimateBtn = document.createElement('button');
            estimateBtn.type = 'button';
            estimateBtn.className = 'mt-2 bg-blue-500 hover:bg-blue-600 text-white text-sm py-1 px-3 rounded';
            estimateBtn.textContent = 'Estimate Price';

            // Create market lookup button
            const marketLookupBtn = document.createElement('button');
            marketLookupBtn.type = 'button';
            marketLookupBtn.className = 'mt-2 ml-2 bg-purple-500 hover:bg-purple-600 text-white text-sm py-1 px-3 rounded';
            marketLookupBtn.textContent = 'Check Market';

            // Create result container
            const estimateResult = document.createElement('div');
            estimateResult.id = 'price-estimate-result';
            estimateResult.className = 'mt-2 text-sm hidden';

            // Add elements to the DOM
            priceContainer.appendChild(estimateBtn);
            priceContainer.appendChild(marketLookupBtn);
            priceContainer.appendChild(estimateResult);

            // Add event listener for basic estimate
            estimateBtn.addEventListener('click', async function () {
                // Gather item details
                const itemDetails = {
                    name: document.getElementById('itemName').value,
                    category: document.getElementById('category').value,
                    condition: document.querySelector('select[name="condition"]').value,
                    description: document.getElementById('description').value,
                    purchaseDate: document.querySelector('input[name="purchaseDate"]').value,
                    predictions: window.imagePredictions || []
                };

                // Check if we have minimum required data
                if (!itemDetails.name || !itemDetails.category || !itemDetails.condition) {
                    estimateResult.innerHTML = `
        <div class="text-yellow-700 bg-yellow-100 p-2 rounded">
          <strong>Missing information:</strong> Please fill in the item name, category, and condition first.
        </div>
      `;
                    estimateResult.classList.remove('hidden');
                    return;
                }

                // Get price estimate
                const estimate = estimateItemPrice(itemDetails);
                displayEstimate(estimate);
            });

            // Add event listener for market lookup
            marketLookupBtn.addEventListener('click', async function () {
                const itemName = document.getElementById('itemName').value;

                if (!itemName) {
                    estimateResult.innerHTML = `
        <div class="text-yellow-700 bg-yellow-100 p-2 rounded">
          <strong>Missing information:</strong> Please enter an item name to search market prices.
        </div>
      `;
                    estimateResult.classList.remove('hidden');
                    return;
                }

                // Show loading state
                this.textContent = 'Searching...';
                this.disabled = true;
                estimateResult.innerHTML = `
      <div class="bg-blue-100 p-2 rounded">
        <div class="flex items-center">
          <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>Checking market prices, please wait...</span>
        </div>
      </div>
    `;
                estimateResult.classList.remove('hidden');

                try {
                    // Get market data
                    const marketData = await getMarketPrices(itemName);

                    // Gather item details for combined estimate
                    const itemDetails = {
                        name: itemName,
                        category: document.getElementById('category').value,
                        condition: document.querySelector('select[name="condition"]').value,
                        description: document.getElementById('description').value,
                        purchaseDate: document.querySelector('input[name="purchaseDate"]').value,
                        predictions: window.imagePredictions || []
                    };

                    // Get enhanced price estimate
                    const enhancedEstimate = enhancedPriceEstimator(itemDetails, marketData);

                    // Display the enhanced estimate
                    displayEnhancedEstimate(enhancedEstimate, marketData);
                } catch (error) {
                    console.error('Error looking up market prices:', error);
                    estimateResult.innerHTML = `
        <div class="bg-red-100 border border-red-200 text-red-800 p-2 rounded">
          <strong>Error:</strong> Could not fetch market prices. Please try again.
        </div>
      `;
                } finally {
                    // Reset button
                    this.textContent = 'Check Market';
                    this.disabled = false;
                }
            });

            // Function to display basic estimate
            function displayEstimate(estimate) {
                let confidenceColor = 'yellow';
                if (estimate.confidence === 'high') {
                    confidenceColor = 'green';
                } else if (estimate.confidence === 'low') {
                    confidenceColor = 'red';
                }

                estimateResult.innerHTML = `
      <div class="bg-${confidenceColor}-100 border border-${confidenceColor}-200 text-${confidenceColor}-800 p-2 rounded">
        <div class="flex justify-between items-center">
          <strong>Estimated Price:</strong> 
          <span class="text-lg font-bold">R${estimate.estimatedPrice}</span>
        </div>
        <div class="text-xs mt-1">
          <div>Confidence: <span class="font-medium">${estimate.confidence}</span></div>
          <div>${estimate.reasoning}</div>
        </div>
        <button id="apply-estimate" class="mt-2 bg-${confidenceColor}-600 hover:bg-${confidenceColor}-700 text-white text-xs py-1 px-2 rounded">
          Apply this estimate
        </button>
      </div>
    `;

                estimateResult.classList.remove('hidden');

                // Add event listener to apply button
                document.getElementById('apply-estimate').addEventListener('click', function () {
                    priceInput.value = estimate.estimatedPrice;
                    estimateResult.classList.add('hidden');
                });
            }

            // Function to display enhanced estimate with market data
            function displayEnhancedEstimate(estimate, marketData) {
                if (!estimate.marketComparison) {
                    // Fall back to basic estimate if no market data
                    displayEstimate(estimate);
                    return;
                }

                const market = estimate.marketComparison;
                const marketSample = marketData.results.prices.slice(0, 5);

                estimateResult.innerHTML = `
      <div class="bg-blue-100 border border-blue-200 text-blue-800 p-3 rounded">
        <div class="flex justify-between items-center">
          <strong>Market-Based Price:</strong> 
          <span class="text-lg font-bold">R${estimate.estimatedPrice}</span>
        </div>
        
        <div class="mt-2 text-xs">
          <div class="font-medium">Market Statistics for "${marketData.searchTerm}":</div>
          <div class="grid grid-cols-4 gap-1 mt-1">
            <div>Lowest: <span class="font-medium">R${market.min}</span></div>
            <div>Highest: <span class="font-medium">R${market.max}</span></div>
            <div>Average: <span class="font-medium">R${market.average}</span></div>
            <div>Median: <span class="font-medium">R${market.median}</span></div>
          </div>
          <div class="mt-1">Based on ${market.sampleSize} similar items</div>
          <div class="mt-1 text-blue-700">Suggested price is ${market.marketPosition}</div>
        </div>
        
        <div class="mt-2">
          <div class="text-sm font-medium">Sample Price Range:</div>
          <div class="relative h-6 bg-blue-200 rounded mt-1">
            <div class="absolute inset-y-0 left-0 bg-blue-500 rounded" 
                 style="width: ${Math.min(100, Math.max(0, (estimate.estimatedPrice - market.min) / (market.max - market.min) * 100))}%">
            </div>
            <div class="absolute inset-y-0 left-0 w-full flex items-center justify-between px-1">
              <span class="text-xs font-bold">R${market.min}</span>
              <span class="text-xs font-bold">R${market.max}</span>
            </div>
          </div>
        </div>
        
        <div class="flex space-x-2 mt-3">
          <button id="apply-market-estimate" class="bg-blue-600 hover:bg-blue-700 text-white text-xs py-1 px-2 rounded">
            Apply this price
          </button>
          <button id="show-similar-items" class="bg-green-600 hover:bg-green-700 text-white text-xs py-1 px-2 rounded">
            Show similar items
          </button>
        </div>
      </div>
    `;

                estimateResult.classList.remove('hidden');

                // Add event listeners
                document.getElementById('apply-market-estimate').addEventListener('click', function () {
                    priceInput.value = estimate.estimatedPrice;
                    estimateResult.classList.add('hidden');
                });

                document.getElementById('show-similar-items').addEventListener('click', function () {
                    // Create and show a modal with similar items
                    showSimilarItemsModal(marketData);
                });
            }

            // Function to display similar items modal
            function showSimilarItemsModal(marketData) {
                // Create modal container
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                modal.id = 'similar-items-modal';

                // Create modal content
                const modalContent = document.createElement('div');
                modalContent.className = 'bg-white rounded-lg shadow-xl p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto';

                // Create items list
                let itemsHtml = '';
                marketData.results.prices.slice(0, 10).forEach((price, index) => {
                    itemsHtml += `
        <div class="py-2 ${index !== 0 ? 'border-t border-gray-200' : ''}">
          <div class="flex justify-between">
            <span class="font-medium">Item ${index + 1}</span>
            <span class="font-bold">R${price}</span>
          </div>
        </div>
      `;
                });

                // Set modal content
                modalContent.innerHTML = `
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold">Similar Items from Takealot</h3>
        <button id="close-modal" class="text-gray-500 hover:text-gray-700">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="mb-3">
        <div class="bg-blue-50 p-3 rounded-md mb-4">
          <div class="font-medium">Price Statistics for "${marketData.searchTerm}"</div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-2">
            <div class="text-center bg-white p-2 rounded shadow-sm">
              <div class="text-xs text-gray-500">Lowest</div>
              <div class="font-bold">R${marketData.results.stats.min}</div>
            </div>
            <div class="text-center bg-white p-2 rounded shadow-sm">
              <div class="text-xs text-gray-500">Average</div>
              <div class="font-bold">R${marketData.results.stats.average}</div>
            </div>
            <div class="text-center bg-white p-2 rounded shadow-sm">
              <div class="text-xs text-gray-500">Median</div>
              <div class="font-bold">R${marketData.results.stats.median}</div>
            </div>
            <div class="text-center bg-white p-2 rounded shadow-sm">
              <div class="text-xs text-gray-500">Highest</div>
              <div class="font-bold">R${marketData.results.stats.max}</div>
            </div>
          </div>
        </div>
        <div class="text-sm mb-2 font-medium">Sample Prices (up to 10):</div>
        ${itemsHtml}
      </div>
      <div class="text-xs text-gray-500 mt-2">
        Note: These prices were extracted from Takealot.com based on your search term.
        Consider these prices when setting your own to be competitive.
      </div>
    `;

                // Add modal to DOM
                modal.appendChild(modalContent);
                document.body.appendChild(modal);

                // Add close event
                document.getElementById('close-modal').addEventListener('click', function () {
                    document.body.removeChild(modal);
                });

                // Close on outside click
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        document.body.removeChild(modal);
                    }
                });
            }
        }

        // Initialize the enhanced price estimator when DOM is loaded
        document.addEventListener('DOMContentLoaded', function () {
            // Call the setup function for the price estimator
            setupPriceEstimator();
        });
    </script>

</body>

</html>