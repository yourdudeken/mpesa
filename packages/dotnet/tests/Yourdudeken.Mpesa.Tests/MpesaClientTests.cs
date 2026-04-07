using Xunit;
using Yourdudeken.Mpesa;
using Yourdudeken.Mpesa.Config;

namespace Yourdudeken.Mpesa.Tests;

public class MpesaClientTests
{
    [Fact]
    public void TestConfigCreation()
    {
        var config = new MpesaConfig
        {
            Environment = "sandbox",
            MpesaConsumerKey = "test_key",
            MpesaConsumerSecret = "test_secret",
            Passkey = "test_passkey",
            Shortcode = "174379",
            InitiatorName = "testapi",
            InitiatorPassword = "test_password"
        };

        Assert.Equal("sandbox", config.Environment);
        Assert.Equal("test_key", config.MpesaConsumerKey);
    }

    [Fact]
    public void TestMpesaInitialization()
    {
        var config = new MpesaConfig
        {
            Environment = "sandbox",
            MpesaConsumerKey = "test_key",
            MpesaConsumerSecret = "test_secret",
            Passkey = "test_passkey",
            Shortcode = "174379",
            InitiatorName = "testapi",
            InitiatorPassword = "test_password"
        };

        var mpesa = new Mpesa(config);
        Assert.NotNull(mpesa);
    }

    [Fact]
    public void TestStaticConstants()
    {
        Assert.Equal("CustomerPayBillOnline", Mpesa.PAYBILL);
        Assert.Equal("CustomerBuyGoodsOnline", Mpesa.TILL);
    }
}