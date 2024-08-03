{{-- Start Wishlist Add Option // --}}
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })

    function addToWishList(course_id) {

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: "/add-to-wishlist/" + course_id,

            success: function(data) {

                // Start Message 

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 6000
                })
                if ($.isEmptyObject(data.error)) {

                    Toast.fire({
                        type: 'success',
                        icon: 'success',
                        title: data.success,
                    })

                } else {

                    Toast.fire({
                        type: 'error',
                        icon: 'error',
                        title: data.error,
                    })
                }

                // End Message   

            }
        })

    }
</script>
{{-- End Wishlist Add Option // --}}

{{-- Start Load Wishlist Data // --}}
<script type="text/javascript">
    function wishlist() {
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/get-wishlist-course/",

            success: function(response) {
                $('#wishQty').text(response.wishQty);

                var rows = ""
                $.each(response.wishlist, function(key, value) {

                    rows += `
                            <div class="col-lg-4 responsive-column-half">
                    <div class="card card-item">
                        <div class="card-image">
                            <a href="/course/details/${value.course.id}/${value.course.course_name_slug}" class="d-block">
                                <img class="card-img-top" src="/${value.course.course_image}" alt="Card image cap">
                            </a>
                            
                        </div><!-- end card-image -->
                        <div class="card-body">
                            <h6 class="ribbon ribbon-blue-bg fs-14 mb-3">${value.course.label}</h6>
                            <h5 class="card-title"><a href="/course/details/${value.course.id}/${value.course.course_name_slug}">${value.course.course_name}</a></h5> 

                            <div class="d-flex justify-content-between align-items-center">
                                
                                ${value.course.discount_price == null 
                                ?`<p class="card-price text-black font-weight-bold">$${value.course.selling_price}</p>`
                                :`<p class="card-price text-black font-weight-bold">$${value.course.discount_price} <span class="before-price font-weight-medium">$${value.course.selling_price}</span></p>`
                                } 
                                
                                <div class="icon-element icon-element-sm shadow-sm cursor-pointer" data-toggle="tooltip" data-placement="top" title="Remove from Wishlist" id="${value.id}" onclick="wishlistRemove(this.id)"><i class="la la-heart"></i></div>
                            </div>
                        </div> 
                    </div> 
                </div> 
                    `
                });
                $('#wishlist').html(rows);

            }
        })
    }
    wishlist();

    function wishlistRemove(id) {
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/wishlist-remove/" + id,

            success: function(data) {

                // Start Message 

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 6000
                })
                if ($.isEmptyObject(data.error)) {
                    Toast.fire({
                        type: 'success',
                        icon: 'success',
                        title: data.success,
                    })
                } else {
                    Toast.fire({
                        type: 'error',
                        icon: 'error',
                        title: data.error,
                    })
                }
                // End Message   
            }
        })
    }
</script>
{{-- End Load Wishlist Data // --}}

{{-- Start Add To Cart  // --}}
<script type="text/javascript">
    function addToCart(courseId, courseName, instructorId, slug) {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                _token: '{{ csrf_token() }}',
                course_name: courseName,
                course_name_slug: slug,
                instructor: instructorId
            },
            url: "/cart/data/store/" + courseId,
            success: function(data) {
                miniCart();
                // Start Message 

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 6000
                })
                if ($.isEmptyObject(data.error)) {

                    Toast.fire({
                        type: 'success',
                        icon: 'success',
                        title: data.success,
                    })

                } else {

                    Toast.fire({
                        type: 'error',
                        icon: 'error',
                        title: data.error,
                    })
                }

                // End Message   

            }
        });
    }
</script>
{{-- End Add To Cart  // --}}

<script type="text/javascript">
    function cart() {
        $.ajax({
            type: "GET",
            url: "/get-cart-course",
            dataType: 'json',
            success: function(response) {
                listCart = "";
                $.each(response.carts, function(key, value) {
                    console.log('khôi');
                    listCart += `       
                        <tr>
                            <th scope="row">
                                <div class="media media-card">
                                    <a href="/course/details/${value.id}/${value.options.slug}" class="media-img mr-0">
                                        <img src="${value.options.image}" alt="Cart image">
                                    </a>
                                </div>
                            </th>
                            <td>
                                <a href="/course/details/${value.id}/${value.options.slug}" class="text-black font-weight-semi-bold">${value.name}</a>
                                <p class="fs-14 text-gray lh-20">By <a href="/instructor/details/${value.options.instructor_info.id}"
                                    class="text-color hover-underline">${value.options.instructor_info.name}</a>
                                </p>
                            </td>
                            <td>
                                <ul class="generic-list-item font-weight-semi-bold">
                                    <li class="text-black lh-18">${value.price}</li>
                                </ul>
                            </td>
                            <td>
                                <button type="button" id="${value.rowId}" onclick="cartRemove(this.id)" class="icon-element icon-element-xs shadow-sm border-0"
                                    data-toggle="tooltip" data-placement="top" title="Remove">
                                    <i class="la la-times"></i>
                                </button>
                            </td>
                        </tr> `;
                });

                $('#cartSubTotal').text(response.cartTotal)

                $("#cartPage").html(listCart);
            }
        });
    }
    cart();
</script>

{{--  GET & REMOVE mini Cart --}}
<script type="text/javascript">
    function miniCart() {
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: '/course/mini/cart',
            success: function(response) {

                $('span[id="cartSubTotal"]').text(response.cartTotal);
                $('#cartQty').text(response.cartQty);

                var miniCart = "";

                $.each(response.carts, function(key, value) {
                    miniCart += `
                        <li class="media media-card">
                            <a href="/course/details/${value.id}/${value.options.slug}" class="media-img">
                                <img src="${value.options.image}" alt="Cart image">
                            </a>
                            <div class="media-body">
                                <h5><a href="/course/details/${value.id}/${value.options.slug}">${value.name}</a></h5>
                                <span class="d-block lh-18 py-1">${value.price}</span>

                                <a type="submit" id="${value.rowId}" onclick="cartRemove(this.id)"><i class="la la-times"></i> </a> 
                            </div>
                        </li>
                `;
                });
                $('#miniCart').html(miniCart);
            }
        })
    }

    miniCart();

    function cartRemove(rowId) {
        $.ajax({
            type: "GET",
            url: "/minicart/course/remove/" + rowId,
            dataType: 'json',
            success: function(data) {
                miniCart();
                couponCalculation();
                cart();
                // Start Message 
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                })
                if ($.isEmptyObject(data.error)) {
                    Toast.fire({
                        type: 'success',
                        icon: 'success',
                        title: data.success,
                    })
                } else {
                    Toast.fire({
                        type: 'error',
                        icon: 'error',
                        title: data.error,
                    })
                }
                // End Message   
            }
        });
    }
</script>

{{-- Apply Coupon --}}
<script type="text/javascript">
    function applyCoupon() {
        var coupon_name = $('#coupon_name').val();
        $.ajax({
            type: "POST",
            dataType: 'json',
            data: {
                coupon_name: coupon_name
            },
            url: "/coupon-apply",

            success: function(data) {

                if (data.validity == true) {
                    $('#couponField').hide();
                    couponCalculation();
                }

                // Start Message 

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                })
                if ($.isEmptyObject(data.error)) {

                    Toast.fire({
                        type: 'success',
                        icon: 'success',
                        title: data.success,
                    })

                } else {

                    Toast.fire({
                        type: 'error',
                        icon: 'error',
                        title: data.error,
                    })
                }
                // End Message   
            }
        })
    }

    /// Start Coupon Calculation Method 
    function couponCalculation() {
        $.ajax({
            type: 'GET',
            url: "/coupon-calculation",
            dataType: 'json',

            success: function(data) {
                console.log(data);
                if (data.total || data.subtotal == 0) {
                    $('#couponCalField').html(
                        `<h3 class="fs-18 font-weight-bold pb-3">Cart Totals</h3>
                <div class="divider"><span></span></div>
                <ul class="generic-list-item pb-4">
                    <li class="d-flex align-items-center justify-content-between font-weight-semi-bold">
                        <span class="text-black">Subtotal:$</span>
                        <span>$${data?.total ?? 0} </span>
                    </li>
                    <li class="d-flex align-items-center justify-content-between font-weight-semi-bold">
                        <span class="text-black">Total:$</span>
                        <span> $${data.total ?? 0}</span>
                    </li>
                </ul>`
                    )

                } else {
                    $('#couponCalField').html(
                        `<h3 class="fs-18 font-weight-bold pb-3">Cart Totals</h3>
                <div class="divider"><span></span></div>
                <ul class="generic-list-item pb-4">
                    <li class="d-flex align-items-center justify-content-between font-weight-semi-bold">
                        <span class="text-black">Subtotal: </span>
                        <span>$${data.subtotal} </span>
                    </li>
                    <li class="d-flex align-items-center justify-content-between font-weight-semi-bold">
                        <span class="text-black">Coupon Name : </span>
                        <span>${data.coupon_name} <button type="button" class="icon-element icon-element-xs shadow-sm border-0" data-toggle="tooltip" data-placement="top" onclick="couponRemove()" >
                            <i class="la la-times"></i>
                        </button></span>
                    </li>


                    <li class="d-flex align-items-center justify-content-between font-weight-semi-bold">
                        <span class="text-black">Coupon Discount:</span>
                        <span> $${data.discount_amount}</span>
                    </li>

                    <li class="d-flex align-items-center justify-content-between font-weight-semi-bold">
                        <span class="text-black">Grand Total:</span>
                        <span> $${data.total_amount}</span>
                    </li> 

                </ul>`
                    )

                }

            }
        })
    }

    couponCalculation();
</script>
{{-- End Apply Coupon --}}

{{-- Remove Coupon --}}
<script type="text/javascript">
    function couponRemove() {
        $.ajax({
            type: "GET",
            url: "/coupon-remove",
            dataType: "json",
            success: function(data) {
                couponCalculation();
                $('#couponField').show();
                // Start Message 
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                })
                if ($.isEmptyObject(data.error)) {
                    Toast.fire({
                        type: 'success',
                        icon: 'success',
                        title: data.success,
                    })
                } else {
                    Toast.fire({
                        type: 'error',
                        icon: 'error',
                        title: data.error,
                    })
                }
                // End Message   
            }
        })
    }
</script>
{{-- End Remove Coupon --}}

{{--  Start Buy Now Button  --}}
<script type="text/javascript">
    function buyCourse(courseId, courseName, instructorId, slug) {
        console.log('khooi');
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/buy/data/store/" + courseId,
            data: {
                _token: '{{ csrf_token() }}',
                course_name: courseName,
                course_name_slug: slug,
                instructor: instructorId
            },
            success: function(data) {
                miniCart();

                // Start Message 
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                })
                if ($.isEmptyObject(data.error)) {
                    Toast.fire({
                        type: 'success',
                        icon: 'success',
                        title: data.success,
                    })
                } else {
                    Toast.fire({
                        type: 'error',
                        icon: 'error',
                        title: data.error,
                    })
                }
                // End Message   
            }
        });
    }
</script>
{{--  End Buy Now Button  --}}
