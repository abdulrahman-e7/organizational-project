<style>
    .form-title {
        font-family: "IBM Plex Sans Arabic" !important;
        font-style: normal;
        font-weight: 700;
        /* font-size: 36px; */
        line-height: 42px;
        color: #fff;
    }

    .hero {
        width: 100%;
        height: 50vh;
        /* background-color: #ED1088; */
        background-image: URL('https://lh3.googleusercontent.com/pw/AM-JKLXva4P7RlMWEJD_UMf699iZq37WokzlPBAqpkLcxYqgkUi3YzPTP5fuglzL3els1W36mjlBVmMNcqjGJMGNtQREe3THVN9pMkRZGNazhM3F5iQSuC4Z435gIA_0xrrPQWa1DGvsV02rmdJBJQxU0XM=w1400-h474-no');
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        display: flex;
        flex-direction: column;
        flex-wrap: nowrap;
        justify-content: center;
        align-items: stretch;
    }

    @media(max-width:768px) {
        .hero {
            height: 50vh;
        }
    }
</style>

<header class="hero cart-banner position-relative">
    <section class="container hero-title ">
        {!! $inner !!}
    </section>
</header>
