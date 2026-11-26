<?php
if(session_status()===PHP_SESSION_NONE){session_start();}
include 'header.php';

$conn=new mysqli("localhost","root","root","grocery_store",3307);
$categories=[];

if(!$conn->connect_error){
    $sql="SELECT id,category_id,category_image,category_name FROM categories ORDER BY id ASC";
    $result=$conn->query($sql);
    if($result&&$result->num_rows>0){
        while($row=$result->fetch_assoc()){
            $categories[]=$row;
        }
    }
    $conn->close();
}
?>

<div class="category-main-container">
<h2 class="section-title">Shop By Categories</h2>
<div class="category-grid">
<?php foreach($categories as $cat){ ?>
<div class="cat-card">
<div class="cat-img">
<img src="dist/images/<?php echo htmlspecialchars($cat['category_image']); ?>" alt="<?php echo htmlspecialchars($cat['category_name']); ?>">
</div>
<h3><?php echo htmlspecialchars($cat['category_name']); ?></h3>
<a href="productListing.php?category_id=<?php echo $cat['category_id']; ?>" class="explore-btn">Explore All</a>
</div>
<?php } ?>
</div>
</div>

<style>
.category-main-container{padding:40px 6%;background:#f8f9fa;min-height:70vh}
.section-title{text-align:center;font-size:32px;color:#176b35;margin-bottom:35px}
.category-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:25px;max-width:1400px;margin:0 auto}
.cat-card{background:#fff;border-radius:14px;padding:22px 15px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,.08);transition:.3s;display:flex;flex-direction:column;align-items:center}
.cat-card:hover{transform:translateY(-5px);box-shadow:0 8px 25px rgba(0,0,0,.15)}
.cat-img{width:100%;height:180px;display:flex;align-items:center;justify-content:center;margin-bottom:15px}
.cat-img img{max-width:100%;max-height:100%;object-fit:contain}
.cat-card h3{font-size:19px;color:#333;margin-bottom:18px;min-height:48px;display:flex;align-items:center;justify-content:center}
.explore-btn{display:inline-block;padding:10px 24px;background:#176b35;color:white!important;text-decoration:none;border-radius:6px;font-weight:bold;transition:.3s;margin-top:auto}
.explore-btn:hover{background:#0f5228}
@media(max-width:1100px){.category-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:800px){.category-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:500px){.category-grid{grid-template-columns:1fr}}
</style>

<?php include 'footer1.php'; ?>