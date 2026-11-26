<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ચેક કરશે કે જો ફાઈલ ડાયરેક્ટ ખુલે છે તો Header અને Footer બતાવશે
$is_standalone = (basename($_SERVER['SCRIPT_FILENAME']) === 'product.php');

if ($is_standalone) {
    include 'header.php';
}

// ================= DATABASE CONNECTION ================= 
$conn = new mysqli( 
    "localhost", 
    "root", 
    "root", 
    "grocery_store", 
    3307 
); 
 
if ($conn->connect_error) { 
    die("Database Connection Failed: " . $conn->connect_error); 
} 
 
// ================= GET CATEGORIES ================= 
$sql = "SELECT id, category_id, category_image, category_name 
        FROM categories 
        ORDER BY id ASC"; 

$result = $conn->query($sql); 
?> 

<section class="categories-section" id="categories"> 
 
    <div class="section-title"> 
        <h2>Shop By Categories</h2> 
        <p>Explore a wide range of fresh and quality grocery categories.</p> 
    </div> 
 
    <div class="category-container"> 
 
        <?php if ($result && $result->num_rows > 0) { ?> 
 
            <?php while ($row = $result->fetch_assoc()) { ?> 
 
                <div class="category-card"> 
 
                    <!-- Category Image --> 
                    <div class="category-image"> 
                        <img 
                            src="dist/images/<?php echo htmlspecialchars($row['category_image']); ?>" 
                            alt="<?php echo htmlspecialchars($row['category_name']); ?>" 
                        > 
                    </div> 
 
                    <!-- Category Details --> 
                    <div class="category-info"> 
                        <h3> 
                            <?php echo htmlspecialchars($row['category_name']); ?> 
                        </h3> 
 
                        <a href="productListing.php?category_id=<?php echo $row['category_id']; ?>" 
                           class="explore-btn"> 
                            Explore Now 
                        </a> 
                    </div> 
 
                </div> 
 
            <?php } ?> 
 
        <?php } else { ?> 
 
            <p style="text-align: center; width: 100%; font-size: 18px; color: #666;">No Categories Found</p> 
 
        <?php } ?> 
 
    </div> 
 
</section> 
 
<style> 
.categories-section { 
    padding: 50px 7%; 
    background: #f8f9fa; 
    min-height: 65vh;
} 
 
.section-title { 
    text-align: center; 
    margin-bottom: 35px; 
} 
 
.section-title h2 { 
    font-size: 32px; 
    color: #176b35;
    margin-bottom: 8px; 
} 
 
.section-title p { 
    color: #555; 
    font-size: 16px;
} 
 
.category-container { 
    display: grid; 
    grid-template-columns: repeat(4, 1fr); 
    gap: 25px; 
} 
 
.category-card { 
    background: #fff; 
    border-radius: 12px; 
    overflow: hidden; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
    transition: 0.3s; 
    display: flex;
    flex-direction: column;
    align-items: center;
} 
 
.category-card:hover { 
    transform: translateY(-5px); 
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
} 
 
.category-image { 
    width: 100%; 
    height: 200px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    background: #fff; 
} 
 
.category-image img { 
    width: 100%; 
    height: 100%; 
    object-fit: contain; 
    padding: 15px; 
} 
 
.category-info { 
    padding: 18px; 
    text-align: center; 
    width: 100%;
} 
 
.category-info h3 { 
    font-size: 20px; 
    color: #333;
    margin-bottom: 15px; 
} 
 
.explore-btn { 
    display: inline-block; 
    padding: 10px 22px; 
    background: #176b35; 
    color: white !important; 
    text-decoration: none; 
    border-radius: 6px; 
    font-weight: 600;
    transition: 0.3s; 
} 
 
.explore-btn:hover { 
    background: #0f5228; 
} 
 
@media (max-width: 992px) { 
    .category-container { 
        grid-template-columns: repeat(2, 1fr); 
    } 
} 
 
@media (max-width: 576px) { 
    .category-container { 
        grid-template-columns: 1fr; 
    } 
} 
</style> 
 
<?php 
$conn->close(); 

if ($is_standalone) {
    include 'footer1.php';
}
?>