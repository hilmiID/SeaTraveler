package com.example.hilmi.seatraveler.Activity;

import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.support.design.widget.BottomSheetBehavior;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import com.example.hilmi.seatraveler.R;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;

public class HomeActivity extends AppCompatActivity {

    private View button;
    private View bottomButton;
    private BottomSheetBehavior<View> bottomSheetBehavior;
    private LinearLayout linearlist;
    private ArrayList<String> pantai = new ArrayList<String>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);
        //set button
        button = findViewById(R.id.got_it);
        bottomButton = findViewById(R.id.bottom_button);
        //check connection
        if (isNetworkConnectionAvailable()){
            //get data from server
            getData();
        } else {
            button.setEnabled(false);
            bottomButton.setEnabled(false);
        }

        bottomSheetBehavior = BottomSheetBehavior.from(findViewById(R.id.bottom_sheet));
        bottomSheetBehavior.setState(BottomSheetBehavior.STATE_HIDDEN);

        bottomButton.setOnClickListener(this::mintaRekomendasi);
        button.setOnClickListener(this::mintaRekomendasi);
    }

    private void mintaRekomendasi(View view) {
        Intent intent = new Intent(this, MintaRekomendasiActivity.class);
        startActivity(intent);
    }

    public void getData(){
        final ProgressDialog progressDialog = new ProgressDialog(this);
        progressDialog.setMessage("Loading...");
        progressDialog.show();
        RequestQueue requestQueue = Volley.newRequestQueue(this);
        String domain = getResources().getString(R.string.url);
        String url = domain+"getListPantai.php";
        Log.e("Isi Get Data",url);

        StringRequest stringRequest = new StringRequest(Request.Method.GET, url, new Response.Listener<String>() {
            @Override
            public void onResponse(String response) {
                try {
                    JSONObject jsonObject = new JSONObject(response);
                    JSONArray jsonArray = jsonObject.getJSONArray("daftar_pantai");
                    for (int a = 0; a < jsonArray.length(); a ++){
                        JSONObject json = jsonArray.getJSONObject(a);
                        pantai.add(json.getString("nama_pantai"));
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                    Log.e("Error json", e.getMessage());
                    progressDialog.dismiss();
                }
                //add textview for list pantai
                linearlist = (LinearLayout) findViewById(R.id.linearlist);
                for (int i=0; i<pantai.size(); i++){
                    TextView textView = new TextView(getBaseContext());
                    textView.setText(pantai.get(i));
                    textView.setTextSize(18);
                    linearlist.addView(textView);
                }
                TextView textView = new TextView(getBaseContext());
                textView.setText("Pilih menu 'Minta Rekomendasi' untuk mendapatkan rekomendasi pantai di Malang.");
                textView.setTextSize(18);
                linearlist.addView(textView);
                progressDialog.dismiss();
            }
        }, new Response.ErrorListener() {

            @Override
            public void onErrorResponse(VolleyError error) {
                Log.e("Error valley","error");
                progressDialog.dismiss();
            }
        });

        requestQueue.add(stringRequest);

    }

    public void checkNetworkConnection(){
        AlertDialog.Builder builder =new AlertDialog.Builder(this);
        builder.setTitle("No internet Connection");
        builder.setMessage("Please turn on internet connection to continue");
        builder.setNegativeButton("close", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.dismiss();
            }
        });
        AlertDialog alertDialog = builder.create();
        alertDialog.show();
    }

    public boolean isNetworkConnectionAvailable(){
        ConnectivityManager cm =
                (ConnectivityManager)getSystemService(Context.CONNECTIVITY_SERVICE);

        NetworkInfo activeNetwork = cm.getActiveNetworkInfo();
        boolean isConnected = activeNetwork != null &&
                activeNetwork.isConnected();
        if(isConnected) {
            Log.d("Network", "Connected");
            return true;
        }
        else{
            checkNetworkConnection();
            Log.d("Network","Not Connected");
            return false;
        }
    }
}
